/*
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */


class filexIndex {

    GRID_ID= "#filex-grid";
    constructor(settings) {
        this.settings = settings;
        this.attachEvents();
    }

    attachEvents() {
        $(document).on('change', '[name="filex-bulk-action[]"]', (e)=> {
            let keys = this.getSelectedRows();
            $('#filex-bulk-actions').toggleClass('collapse', (!keys.length > 0));

            let params = {};
            keys.forEach((e, i, a) => {
                params['uuids[' + i + ']'] = e;
            });
            $("#filex-bulk-delete,#filex-bulk-acl,#filex-bulk-download").data('params', params);
        })
    }

    getSelectedRows() {
        var $grid = $(this.GRID_ID);
        var keys = [];
            $grid.find('[name="filex-bulk-action[]"]:checked').each(function () {
                keys.push($(this).val());
            });

        return keys;
    }


}

if (!filexIndexInstance) {
    var filexIndexInstance = new filexIndex();
}
