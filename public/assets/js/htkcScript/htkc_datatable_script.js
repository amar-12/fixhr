/**
 * Javascript Custom Datatable Script
 *
 * @subpackage  Custom Script
 * @category	Javascript
 * @author		Hemant Chandra
 */

function datatable_with_export({tableId, url}) {
    return $('#' + tableId).DataTable({
        dom: '<"row"<"col-sm-3"B><"col-sm-8"><"col-sm-1">><"col-sm-12"rt><"row"<"col-sm-6"i><"col-sm-6"p>>',
        processing: true,
        serverSide: true,
        stateSave: true,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        pageLength: 10,
        order: [[0, 'desc']],
        ajax: url,
        rowCallback: function(row) {
            $('td', row).css('font-size', '12px');
        },
        buttons: [
            {
                extend: 'collection',
                className:'btn btn-primary',
                text: 'Export As',
                buttons: [
                    { extend: 'copy', className: 'export-btn' },
                    { extend: 'excel', className: 'export-btn' },
                    { extend: 'pdf', className: 'export-btn' },
                    { extend: 'print', className: 'export-btn' },
                ],
            },
        ],
        language: {
            searchPlaceholder: "Search here...",
            processing: '<div id="lottieAnimationContainer"><iframe src="https://lottie.host/embed/d472f9ed-9c55-43dc-a890-de66a74d03a1/ibWStEF4Sm.json"></iframe></div>',
            // paginate: {
            //     next: '&#8594;', // or '→'
            //     previous: '&#8592;' // or '←'
            // }
        },
    });
}

function datatable_with_buttons({tableId, url}) {
    $('#' + tableId).DataTable({
        dom: '<"row"<"col-sm-1"B><"col-sm-3"l><"col-sm-6"f><"col-sm-2"<"custom-button text-end">>><"col-sm-12"rt><"row"<"col-sm-6"i><"col-sm-6"p>>',
        processing: true,
        serverSide: true,
        stateSave: true,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        pageLength: 5,
        order: [[0, 'desc']],
        ajax: url,
        rowCallback: function(row) {
            $('td', row).css('font-size', '12px');
        },
        buttons: [
            {
                extend: 'collection',
                className:'btn btn-primary',
                text: 'Export As',
                buttons: [
                    { extend: 'copy', className: 'export-btn' },
                    { extend: 'excel', className: 'export-btn' },
                    { extend: 'pdf', className: 'export-btn' },
                    { extend: 'print', className: 'export-btn' },
                ],
            },
        ],
        language: {
            searchPlaceholder: "Search here...",
            processing: '<iframe src="https://lottie.host/embed/d472f9ed-9c55-43dc-a890-de66a74d03a1/ibWStEF4Sm.json"></iframe>'
        },
    });
}


function datatable_custom_buttons({tableId, url}) {
    var table = $('#' + tableId).DataTable({
        dom: '<"row"<"col-sm-1"B><"col-sm-3"l><"col-sm-3"<"custom-dropdown">><"col-sm-3"f><"col-sm-2"<"custom-button text-end">>><"col-sm-12"rt><"row"<"col-sm-6"i><"col-sm-6"p>>',
        processing: true,
        serverSide: true,
        stateSave: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        order: [[0, 'desc']],
        ajax: url,
        rowCallback: function(row) {
            $('td', row).css('font-size', '12px');
        },
        buttons: [
            {
                extend: 'collection',
                className: 'btn btn-primary',
                text: 'Export As',
                buttons: [
                    { extend: 'copy', className: 'export-btn' },
                    { extend: 'excel', className: 'export-btn' },
                    { extend: 'pdf', className: 'export-btn' },
                    { extend: 'print', className: 'export-btn' },
                ],
            },
        ],
        language: {
            searchPlaceholder: "Search here...",
            processing: '<div id="lottieAnimationContainer"><iframe src="https://lottie.host/embed/d472f9ed-9c55-43dc-a890-de66a74d03a1/ibWStEF4Sm.json"></iframe></div>',
            // sProcessing: "Processing...",
            // sLengthMenu: "Show _MENU_ entries",
            // sZeroRecords: "No records found",
            // sInfo: "Showing _START_ to _END_ of _TOTAL_ entries",
            // sInfoEmpty: "Showing 0 entries",
            // sInfoFiltered: "(filtered from _MAX_ total entries)",
            // sInfoPostFix: "",
            // sSearch: "Search employees:",
            // searchPlaceholder: "Name/SSN/Employee No.",
            // sEmptyTable: "No data available in table",
            // sInfoThousands: ",",
            // sLoadingRecords: "Loading...",
            // oPaginate: {
            //     sFirst: "First",
            //     sLast: "Last",
            //     sNext: "Next",
            //     sPrevious: "Previous"
            // }
        },
    });

    return table;
}

function action_submit({data, form_id, tableId = '', dataUrl = '', buttons = false}) {
    $.ajax({
        type: 'POST',
        url: '',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            $('#'.concat(form_id))[0].reset();

            $('#loader').html('<div class="cardheader"></div>');

            setTimeout(function() {
                if (buttons) {
                    datatable_with_buttons(tableId, dataUrl);
                } else {
                    datatable(tableId, dataUrl);
                }
            }, 500)
        }
    });
}

function common_action_function({url, tableId, dataUrl, buttons = false, card_id = ''}) {
    $.ajax({
        type: 'POST',
        url: url,
        cache: false,
        success: function(data) {
            $('#loader').html('<div class="cardLoader"></div>');

            document.getElementById(card_id).style.display = 'none';
        },
        complete: function() {
            setTimeout(function() {
                if (buttons) {
                    datatable_with_buttons(tableId, dataUrl);
                } else {
                    datatable(tableId, dataUrl);
                }
                document.getElementById(card_id).style.display = 'block';
            }, 500);
        }
    });

}
