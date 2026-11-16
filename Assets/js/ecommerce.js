Mautic.ecommerceBindTimelineOrderLinks = function (container) {
    var scope = container || 'body';

    mQuery(scope).find('a.js-ecommerce-order-timeline').each(function () {
        var $link = mQuery(this);
        var $detailsRow = $link.closest('tr.timeline-details');
        var $mainRow = $detailsRow.prevAll('tr.timeline-row').not('.timeline-date-header').first();

        if (!$mainRow.length || !$link.attr('href')) {
            return;
        }

        $mainRow.find('.table-expand-button').addClass('hide');
        $mainRow.css('cursor', 'pointer');
        var title = $link.attr('data-order-title');
        if (title) {
            $mainRow.attr('title', title);
        }

        $mainRow.off('click.ecommerceOrder').on('click.ecommerceOrder', function (event) {
            if (mQuery(event.target).closest('a, button, .table-expand').length) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            // Open after this click finishes. Opening the shared modal during the
            // click lets Bootstrap treat that same click as a backdrop dismiss, which
            // aborts the request and leaves the modal empty on Mautic 7.1.
            window.setTimeout(function () {
                var $modal = mQuery('#MauticSharedModal');
                var $dialog = $modal.find('.modal-dialog');
                $dialog.addClass('modal-lg');
                $modal.one('hidden.bs.modal.ecommerceOrderSize', function () {
                    $dialog.removeClass('modal-lg');
                });
                $link.trigger('click');
            }, 0);
        });
    });
};

Mautic.ecommerceInitContactTab = function () {
    var $pane = mQuery('#ecommerce-container');
    if (!$pane.length) {
        return;
    }

    var $tabContent = $pane.closest('.tab-content');
    if ($tabContent.length) {
        $tabContent.append($pane);
    }

    mQuery('body > .modal[id^="ecommerce-order-modal-"]').remove();
    $pane.find('.modal').appendTo(document.body);
};

mQuery(document).on('click.ecommerceTab', 'a[href="#ecommerce-container"][data-toggle="tab"]', function () {
    window.setTimeout(function () {
        var $pane = mQuery('#ecommerce-container');
        if (!$pane.length || $pane.hasClass('active')) {
            return;
        }

        $pane.closest('.tab-content').children('.tab-pane.active').removeClass('active in');
        $pane.addClass('active in');
    }, 200);
});

mQuery(document).on('mautic:onPageLoad:before', function (event, container) {
    Mautic.ecommerceBindTimelineOrderLinks(container);
    Mautic.ecommerceInitContactTab();
});
