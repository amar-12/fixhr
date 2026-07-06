function initManagerSelect2(selector, placeholder, extra_params = {}) {
    $(selector).select2({
        placeholder: placeholder || 'Search...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: "/ajax/managers",
            type: "GET",
            dataType: "json",
            delay: 250,
            data: function(params) {
                return { search: params.term || '', extra_params: extra_params };
            },
            processResults: function(data) {
                return {
                    results: data.map(i => ({ id: i.id, text: i.name }))
                };
            },
            error: function(xhr, status, error){
                console.error("Select2 AJAX Error:", error);
            }
        }
    });
}