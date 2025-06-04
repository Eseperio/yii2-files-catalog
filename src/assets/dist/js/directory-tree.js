/**
 * DirectoryTree jQuery plugin
 *
 * This plugin handles the dynamic loading of directories and files in the directory tree widget.
 * Works with a virtual filesystem based on inodes instead of real directories.
 */
(function ($) {
    'use strict';

    $.fn.directoryTree = function (options) {
        var defaults = {
            ajaxUrl: '',
            multiple: false,
            mode: 2, // MODE_ALL
            extensions: [],
            rootNodeUuid: null,
            i18n: {
                loading: 'Loading...',
                emptyDirectory: 'Empty directory',
                errorLoading: 'Error loading directory'
            }
        };

        var settings = $.extend(true, {}, defaults, options);

        return this.each(function () {
            var $container = $(this);
            var $input = $('#' + settings.id);
            var selectedItems = $input.val() ? $input.val().split(',') : [];

            // Initialize the tree
            initTree();

            /**
             * Initialize the tree
             */
            function initTree() {
                // Load the root directory
                loadDirectory(settings.rootNodeUuid, $container.find('> ul'));

                // Handle toggle click
                $container.on('click', '.toggle-icon', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var $icon = $(this);
                    var $li = $icon.closest('li');
                    var $ul = $li.find('> ul');

                    if ($ul.length) {
                        // Toggle visibility without reloading
                        $ul.toggle();
                        $icon.text($ul.is(':visible') ? '▼' : '▶');
                    } else {
                        // Load directory contents (first time only)
                        $li.addClass('loading-children');
                        $icon.text('...');
                        var uuid = $li.data('uuid');
                        $li.append('<ul><li class="loading">' + getMessage('loading') + '</li></ul>');
                        loadDirectory(uuid, $li.find('> ul'));
                    }
                });

                // Handle selection by clicking on the node label
                $container.on('click', '.node-label', function (e) {
                    e.preventDefault();

                    var $label = $(this);
                    var $li = $label.closest('li');
                    var uuid = $li.data('uuid');
                    var isDir = $li.data('is-dir');
                    var extension = $li.data('extension');
                    var $checkbox = $li.find('input[type="checkbox"], input[type="radio"]');

                    // Only allow selection based on mode
                    if ((settings.mode === 1 && !isDir) ||
                        (settings.extensions.length > 0 && !isDir && !isValidExtension(extension))) {
                        return;
                    }

                    if (settings.multiple) {
                        // Toggle selection
                        if ($li.hasClass('selected')) {
                            $li.removeClass('selected');
                            $checkbox.prop('checked', false);
                            selectedItems = selectedItems.filter(function (item) {
                                return item !== uuid;
                            });
                        } else {
                            $li.addClass('selected');
                            $checkbox.prop('checked', true);
                            selectedItems.push(uuid);
                        }
                    } else {
                        // Single selection
                        $container.find('.selected').removeClass('selected');
                        $container.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
                        $li.addClass('selected');
                        $checkbox.prop('checked', true);
                        selectedItems = [uuid];
                    }

                    // Update the input value
                    $input.val(selectedItems.join(','));
                });

                // Handle selection by clicking on the checkbox/radio
                $container.on('change', 'input[type="checkbox"], input[type="radio"]', function (e) {
                    var $checkbox = $(this);
                    var $li = $checkbox.closest('li');
                    var uuid = $li.data('uuid');

                    if (settings.multiple) {
                        // Multiple selection mode
                        if ($checkbox.prop('checked')) {
                            $li.addClass('selected');
                            if (selectedItems.indexOf(uuid) === -1) {
                                selectedItems.push(uuid);
                            }
                        } else {
                            $li.removeClass('selected');
                            selectedItems = selectedItems.filter(function (item) {
                                return item !== uuid;
                            });
                        }
                    } else {
                        // Single selection mode
                        $container.find('.selected').removeClass('selected');
                        $li.addClass('selected');
                        selectedItems = [uuid];
                    }

                    // Update the input value
                    $input.val(selectedItems.join(','));
                });
            }

            /**
             * Load directory contents via AJAX
             *
             * @param {string} uuid The directory UUID
             * @param {jQuery} $container The container to append the contents to
             */
            function loadDirectory(uuid, $container) {
                $.ajax({
                    url: settings.ajaxUrl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        uuid: uuid,
                        mode: settings.mode,
                        extensions: settings.extensions
                    },
                    success: function (response) {
                        $container.empty();

                        // Actualizar el icono del directorio padre a la flecha hacia abajo
                        var $parentLi = $container.closest('li');
                        if ($parentLi.length) {
                            $parentLi.removeClass('loading-children');
                            $parentLi.find('> .node-content > .toggle-icon').text('▼');
                        }

                        // if not successful, show error
                        if (!response.success) {
                            $container.append('<li class="error">' + response.message + '</li>');
                            return;
                        }

                        if (response.items.length === 0) {
                            $container.append('<li class="empty">' + getMessage('emptyDirectory') + '</li>');
                            return;
                        }

                        $.each(response.items, function (i, item) {
                            var $li = $('<li></li>')
                                .attr('data-uuid', item.uuid)
                                .attr('data-is-dir', item.isDir)
                                .attr('data-extension', item.extension);

                            // Crear un contenedor para los elementos del nodo
                            var $nodeContent = $('<div class="node-content"></div>');

                            var $toggle = $('<span class="toggle-icon"></span>');
                            var $icon = $('<span></span>').addClass('file-icon-container');
                            var $label = $('<span class="node-label"></span>').text(item.name);

                            if (item.isDir) {
                                $toggle.text('▶');
                                // Usar icono de directorio
                                $icon.html('<div class="fiv-sqo fiv-icon-folder"></div>');
                            } else {
                                $toggle.text('');
                                // Obtener el icono según la extensión del archivo
                                var extension = (item.extension || '').toLowerCase();
                                if (!extension) {
                                    extension = 'blank';
                                }
                                $icon.html('<div class="fiv-sqo fiv-icon-' + extension + '"></div>');
                            }

                            // Add checkbox or radio button
                            var inputType = settings.multiple ? 'checkbox' : 'radio';
                            var $input = $('<input type="' + inputType + '" name="selection[]" />').val(item.uuid);

                            // Check if this item is selected
                            if (selectedItems.indexOf(item.uuid) !== -1) {
                                $li.addClass('selected');
                                $input.prop('checked', true);
                            }

                            // Añadir todos los elementos al contenedor del nodo
                            $nodeContent.append($input).append($toggle).append($icon).append($label);
                            $li.append($nodeContent);
                            $container.append($li);
                        });
                    },
                    error: function () {
                        $container.empty().append('<li class="error">' + getMessage('errorLoading') + '</li>');

                        // En caso de error, restaurar el icono de toggle al estado original
                        var $parentLi = $container.closest('li');
                        if ($parentLi.length) {
                            $parentLi.removeClass('loading-children');
                            $parentLi.find('> .node-content > .toggle-icon').text('▶');
                        }
                    }
                });
            }

            /**
             * Check if a file has a valid extension
             *
             * @param {string} extension The file extension
             * @return {boolean} Whether the file has a valid extension
             */
            function isValidExtension(extension) {
                if (settings.extensions.length === 0) {
                    return true;
                }

                return settings.extensions.indexOf(extension.toLowerCase()) !== -1;
            }
            
            /**
             * Get translated message or default message if translation is not available
             * 
             * @param {string} key The message key
             * @return {string} The translated message
             */
            function getMessage(key) {
                return settings.i18n[key] || defaults.i18n[key];
            }
        });
    };
})(jQuery);
