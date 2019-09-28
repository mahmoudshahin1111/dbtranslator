<?php

namespace LiteMs\Translator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TranslationController extends Controller
{
    private $language_model;
    private $defualt_langauge_id;
    private $language_table_name;
    private $translation_table_name;

    public function __construct()
    {
        $this->resolveDefualtLanguage();
    }
    private function resolveDefualtLanguage()
    {
        $this->language_model = Config::get('translation.language_model');
        $this->defualt_langauge_id = Config::get('translation.language_id');
        $this->language_table_name = Config::get('translation.language_table_name');
        $this->translation_table_name = Translation::TABLE_NAME;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            switch ($request['resource']) {
                case 'langs':
                    $langs = $this->language_model::where('id', "!=", $this->defualt_langauge_id)->get(['id', 'name']);
                    return $langs;
                case 'translation':
                    $request->validate([
                        'lang' => ['required', "exists:{$this->language_table_name},id"]
                    ]);
                    $lang_id = request('lang') ?? $this->defualt_langauge_id;
                    $collection = [];
                    $translations = Translation::all(['id', 'table_name', 'table_db_name']);
                    foreach ($translations as $trans) {
                        $a = DB::select("SELECT count(id) as records from {$trans->table_db_name} where `source_id` is null limit 1")[0];
                        $x = DB::select("SELECT count(id) as translated FROM {$trans->table_db_name} WHERE `source_id`is not null and lang_id = ? limit 1", [$lang_id])[0];
                        array_push($collection, [$trans->id, $trans->table_name, $a->records, $x->translated]);
                    }
                    return $collection;
                case 'translation_words':
                    return $this->getTranslation($request);
            }
            return $this->getResources();
        }
        return view('trans::index');
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'trans_id' => ['required', 'exists:translations,id'],
            'lang_id' => ['required', "exists:{$this->language_table_name},id"]
        ]);
        if ($v->fails()) {
            return Response::noContent(404);
        }
        $trans_id = $request['trans_id'] ?? null;
        $lang_id = $request['lang_id'] ?? $this->defualt_langauge_id;
        $trans = Translation::find($trans_id);
        $table_name = $trans->table_db_name ?? null;
        $col_db_names = explode('|', $trans->trans_cols);
        if (!isset($trans_id) || !isset($lang_id) || !isset($table_name)) {
            return Response::noContent(404);
        }
        foreach ($request->word as $parent_id => $inputs) {
            $child_id = $this->hasTranslate($table_name, $parent_id, $lang_id);
            $p = DB::table($table_name)->where('id', $parent_id)->first();
            $new_id = $this->nextId($table_name, $p);
            foreach ($p as $col => $v) {
                $query_insert[$col] = $v;
            }
            $query_insert['id'] = $new_id;
            $query_insert['source_id'] = $parent_id;
            $query_insert['lang_id'] = $lang_id;
            $query_insert['source_id'] = $parent_id;
            $query_insert['created_at'] = Carbon::now();
            $query_insert['updated_at'] = Carbon::now();
            foreach ($col_db_names as $i => $col_n) {
                if (!empty($child_id)) {
                    DB::table($table_name)->where('id', $child_id[0]->id)->update([$col_n => $inputs[$i]]);
                } else {
                    $query_insert[$col_n] = $inputs[$i];
                }
            }
            foreach ($inputs as $key => $v) {
                if ($v == null) {
                    unset($inputs[$key]);
                }
            }
            if (empty($child_id) && !empty($inputs)) {
                DB::table($table_name)->insert($query_insert);
            }
        }
        return Response::json(true);
    }
    private function hasTranslate($table_name, $parent_id, $lang_id)
    {
        $query = "SELECT f2.id FROM {$table_name} as f1 inner join {$table_name} as f2 on f1.`source_id` is null and f2.`source_id` = f1.`id` where f2.lang_id = {$lang_id} and f1.id = {$parent_id} limit 1";
        return DB::select($query);
    }
    private function nextId($table_name)
    {
        return DB::selectOne("select id from $table_name ORDER by $table_name.id DESC limit 1")->id + 1;
    }
    public function show(Request $request, $id)
    {
        return view('trans::trans', ['lang' => request('lang'), 'trans_id' => $id]);
    }
    public function getTranslation(Request $request)
    {
        $v = Validator::make($request->all(), [
            'trans_id' => ['required', 'exists:translations,id'],
            'lang' => ['required', "exists:{$this->language_table_name},id"]
        ]);
        if ($v->fails()) {
            return Response::noContent(404);
        }
        $collection = [];
        $lang_id = request('lang');
        $trans_id = request('trans_id');
        $trans_base = Translation::find($trans_id);
        $table_name = $trans_base->table_db_name;
        $table_col_trans = explode('|', $trans_base->trans_cols);
        $table_col_as = explode('|', $trans_base->cols_as);
        $show = ["f1.id as base_id", "f2.id as trans_id"];
        foreach ($table_col_trans as $i => $col) {
            array_push($show, "f1.{$col} as base_{$table_col_as[$i]}");
            array_push($show, "f2.{$col} as trans_{$table_col_as[$i]}");
        }
        $show = implode(',', $show);
        $records = DB::select("SELECT {$show} FROM {$table_name} as f1 left join {$table_name} as f2 on  f2.`source_id` = f1.id and f2.`lang_id` = {$lang_id}
        where f1.`source_id` is null");
        //
        foreach ($records as $record) {
            array_push($collection, $record);
        }
        return Response::json($collection);
    }
}
