/*
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */


class filexIndex {

    constructor(settings) {
        this.settings = settings;
        this.attachEvents();
    }

    attachEvents() {
        $(document).on('change', '[name="filex-bulk-action[]"]', function (e) {
            let keys = $('#filex-grid').yiiGridView('getSelectedRows');
            $('#filex-bulk-actions').toggleClass('collapse', (!keys.length > 0))

            let params = {};
            keys.forEach((e, i, a) => {
                params['uuids[' + i + ']'] = e;
            });
            $("#filex-bulk-delete,#filex-bulk-acl").attr('data-params', JSON.stringify(params));
        })
    }


}

if (!filexIndexInstance) {
    var filexIndexInstance = new filexIndex();
}
