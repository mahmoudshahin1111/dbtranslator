@extends('trans::layout')
@push('content')
<div class="container">
    <div class="row border border-primary">
        <h1 class="col-12">
            DataBase Transelator
        </h1>

        <div class="col-6">
            <label for="language">Choose Language</label>
            <select class="form-control" name="language" id="language"></select>
            <small id="helpId" class="form-text text-muted">This Language We Will Translating To From Original Defualt Language</small>
        </div>

        <div class="col-12 mt-5 mb-5">
            <table id="base_table" class="table table-striped cell-border">
            </table>
        </div>
    </div>
</div>

@endpush

@push('js')
<script>
    var table = null;
    var url = "{{url('/')}}";

    function initPage() {
        initTable();
        initPageEvents();
        drawLanguageSelect();
    }

    function initTable() {
        this.table = $('#base_table').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "autoWidth": false,
            "scrollX": true,
            columns: [{
                    title: "#"
                },
                {
                    title: "Name"
                },
                {
                    title: "All Records"
                },
                {
                    title: "Record Translated",
                    "render": function(data, type, row, meta) {
                        if (data == 0) {
                            return `<span class="text-danger">${data}</span>`;
                        }
                        return `<span>${data}</span>`;
                    }
                },
                {
                    data: null,
                    title: "Translation",
                    "render": function(data, type, row, meta) {
                        return `<a onclick="redirectTo('${url}/translation/${row[0]}/${$('#language').val()}')" class="btn btn-block btn-info">To Translate</a>`;
                    }
                }
            ]
        });
    }

    function redirectTo(urlTo) {
        window.location.href = urlTo;
    }
    //resource handle
    function getSource(url, data, on_success, on_error) {
        $.ajax({
            "url": url,
            "data": data,
            success: function(trans_data) {
                if (trans_data != null) {
                    on_success(trans_data);
                }
            },
            error: function(error) {
                if (error != null) {
                    on_error(error);
                }
            }
        });
    }
    //draw elements
    function drawLanguageSelect() {
        let next = function(langs) {
            if (langs == null || langs.length == 0) {
                return false;
            }
            lang_select = $('#language').html('<option value>Choose</option>');
            $.each(langs, function(i, r) {
                $(lang_select).append(`<option value="${r.id}">${r.name}</option>`);
            });
        }
        getSource(url, {
            "resource": "langs"
        }, next, () => {});
    }

    function drawDataInTable(language) {
        if (!language) {
            table.clear().draw();
            return false;
        }
        let next = function(trans_data) {
            if (trans_data.length <= 0) {
                return false;
            } else {
                table.clear().draw();
                table.rows.add(trans_data);
                table.columns.adjust().draw();
            }
        }

        getSource(url, {
            "resource": "translation",
            'lang': language
        }, next, () => {});
    }
    //events
    function initPageEvents() {
        $('#language').on('change', function(e) {
            drawDataInTable($(this).val());
        });
    }
    $(function() {
        initPage();
    });
</script>

@endpush
