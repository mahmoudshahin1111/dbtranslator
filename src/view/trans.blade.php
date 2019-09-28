@extends('trans::layout')

@push('content')
<div class="container">
    <h1 class="row">
        Translation
    </h1>
    <form id="from_table">
        @csrf
        <div class="form-group mt-5 mb-5">
            <input type="hidden" name="trans_id" value="{{$trans_id}}">
            <input type="hidden" name="lang_id" value="{{$lang}}">
            <table id="base_table" class="cell-border">
            </table>
        </div>
        <div class="form-group row  d-flex justify-content-center">
            <div class=" col-6">
            <button id="submit_XoIaA21" type="submit" class="btn btn-block btn-success submit">Translate</button>
            <a href="{{url('/')}}" class="btn btn-block btn-info" href="#" role="button">Back</a>
            </div>
        </div>
    </form>
</div>
@endpush

@push('js')
<script>
    var table = null;
    var default_lang = "{{$lang}}";
    var trans_id = "{{$trans_id}}";
    var url = "{{url('/')}}";
    const TRANSLATION_LABELS = {
        BASE_TRANS: "Word",
        SEC_TRANS: "Translation",
        SUBMIT_BUTTON: "Translate",
        SUBMIT_LOADING: `<div class="spinner-border text-primary" role="status"><span class="sr-only">Translate</span></div>`,
    }
    var columns = [{
            data: "base_id",
            title: "#"
        },
        {
            data: "trans_id",
            title: "Translation ID",
            "visible": false
        }
    ];

    function initPage() {
        drawWordsTable();
    }
    /* =========================== */
    function drawWordsTable() {
        $('#submit_XoIaA21').html(`${TRANSLATION_LABELS.SUBMIT_LOADING}`);
        let next = function(words) {
            if (words.length <= 0) {
                return false;
            }
            $.each(words[0], function(c, v) {
                if (c.includes('id')) {
                    return true;
                }
                rs = c.split('_', 2);
                if (rs[0] == 'base') {
                    rs = `${TRANSLATION_LABELS.BASE_TRANS} ${rs[1]}`;
                    col = {
                        data: c,
                        title: rs,
                    }
                } else {
                    rs = `${TRANSLATION_LABELS.SEC_TRANS} ${rs[1]}`;
                    col = {
                        data: c,
                        title: rs,
                        "render": function(data, type, row, meta) {
                            if (data == null || row.trans_id == null) {
                                return `<input type="text" name="word[${row.base_id}][]" value="" class="form-control"/>`
                            } else {
                                return `<input type="text" name="word[${row.base_id}][]" value="${data}" class="form-control"/>`
                            }
                        }
                    }
                }
                columns.push(col);
            });
            initTable();
            table.clear().draw();
            table.rows.add(words);
            table.columns.adjust().draw();
            $('#submit_XoIaA21').html(`${TRANSLATION_LABELS.SUBMIT_BUTTON}`);
        }
        getTranslationWords(next);
    }
    /* ============================== */
    function initTable() {
        this.table = $('#base_table').DataTable({
            "paging": true,
            "searching": false,
            "info": false,
            "autoWidth": false,
            "scrollX": true,
            "columns": columns
        });
    }

    function getTranslationWords(on_finish) {
        $.ajax({
            "url": url,
            "data": {
                'resource': 'translation_words',
                "trans_id": trans_id,
                "lang": default_lang
            },
            success: function(words) {
                if (words != null) {
                    on_finish(words);
                }

            },
            error: function(error) {

            },
        });
    }

    function sendData(on_finish) {
        form_data = $('#from_table').serialize();
        $.ajax({
            "url": url,
            "data": form_data,
            "type": "post",
            success: function(res) {
                if (on_finish != null) {
                    on_finish(res);
                }

            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function initActions() {
        //Link Objects To Elements
        $('#from_table').on('submit', function(e) {
            e.preventDefault();
            sendData(function(msg) {
                console.log(msg);
            });
        });
    }

    function ajaxEvents() {
        $(document).ajaxSend((e) => {
            $('#submit_XoIaA21').html(`${TRANSLATION_LABELS.SUBMIT_LOADING}`);
        });
        $(document).ajaxComplete(e => {
            $('#submit_XoIaA21').html(`${TRANSLATION_LABELS.SUBMIT_BUTTON}`);
        });
    }
    $(function() {
        ajaxEvents();
        initActions();
        initPage();
    });
</script>

@endpush
