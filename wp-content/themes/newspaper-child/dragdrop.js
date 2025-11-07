jQuery(function ($) {
    // Drag and Drop

    function updateEmptyState() {
        $('.td_block_inner').each(function () {
            var hasItems = $(this).find('.td-cpt-post').length > 0;
            $(this).toggleClass('td-empty', !hasItems);
        });
    }

    window.initiateDragAndDrop = function (callback) {

        // Kald én gang ved load
        updateEmptyState();

        $('.dragAndDrop .td_block_inner').sortable({
            items: '.td-cpt-post',
            connectWith: '.dragAndDrop .td_block_inner',
            placeholder: 'td-cpt-placeholder',
            forcePlaceholderSize: true,

            // Blank helper
            helper: function (event, item) {
                var $helper = $('<div class="td-drag-helper"></div>');
                return $helper;
            },

            start: function (event, ui) {
                ui.item.data('fromParent', ui.item.parent());
            },

            stop: function (event, ui) {
                var $fromParent = ui.item.data('fromParent');
                var $toParent = ui.item.parent();

                // Opdater tomme/ikke-tomme lister
                updateEmptyState();

                callback($fromParent, $toParent, ui.item);
            }
        });
    }
});