/*
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */


class filexIndex {

    GRID_ID = "#filex-grid";

    lastRowSelectedIdx;

    settings = {
        //Class to be applied for those rows selected
        activeRowClass: 'info'
    };

    constructor(settings) {
        this.settings = $.extend(this.settings, settings);
        this.attachEvents();
    }

    attachEvents() {
        let checkBoxSelector = '[name="filex-bulk-action[]"]';
        $(document)
            //Handle click on row
            .on('click', '#filex-grid tr td', function (e) {
                if (['a', 'button', 'input'].indexOf(e.target.tagName) >= 0)
                    return;

                let checkBox = $(this).parent().find(checkBoxSelector);
                checkBox.prop('checked', !checkBox.prop('checked'));
                if (e.shiftKey) {
                    let shiftClick = jQuery.Event('click');
                    shiftClick.shiftKey = true;
                    checkBox.trigger(shiftClick);
                } else {
                    checkBox.click();
                }

                checkBox.click();
            })
            // Handle click on checkboxes
            .on('click', checkBoxSelector, (e) => {
                e.stopPropagation();
                let keys = this.getSelectedRows();
                $('#filex-bulk-actions').toggleClass('collapse', (!keys.length > 0));
                let row = $(e.currentTarget).closest('tr');
                let params = {};
                keys.forEach((e, i, a) => {
                    params['uuids[' + i + ']'] = e;
                });

                let rowRealObj = row.get(0),
                    allRows = [...rowRealObj.parentElement.children],
                    currentIndex = allRows.indexOf(rowRealObj);

                if (e.shiftKey) {
                    allRows.forEach((e, i) => {
                        if (this.lastRowSelectedIdx < currentIndex) {
                            if (i > this.lastRowSelectedIdx && i < currentIndex) {
                                $(e).find(checkBoxSelector).prop('checked', true).change()
                            }
                        } else if (this.lastRowSelectedIdx > currentIndex) {
                            if (i < this.lastRowSelectedIdx && i > currentIndex) {
                                $(e).find(checkBoxSelector).prop('checked', true).change()
                            }
                        }
                    });
                } else {
                    this.lastRowSelectedIdx = currentIndex;
                }
                $("#filex-bulk-delete,#filex-bulk-acl,#filex-bulk-download").data('params', params);
            }).on('change', checkBoxSelector, (e) => {
            let row = $(e.currentTarget).closest('tr');
            row.toggleClass(this.settings.activeRowClass, $(e.currentTarget).prop('checked'));
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
