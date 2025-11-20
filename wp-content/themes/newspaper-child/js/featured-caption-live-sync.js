(function () {
    // Hvilket felt skal vi skrive billedteksten ind i?
    // Juster selector hvis du bruger ACF el.lign.
    function getCaptionInput() {
        return (
            document.querySelector('input[name="featured_caption"]') ||
            document.querySelector('textarea[name="featured_caption"]') ||
            document.querySelector('[data-name="featured_caption"] input') ||
            document.querySelector('[data-name="featured_caption"] textarea')
        );
    }

    // Læs nuværende featured image ID fra hidden input
    function getCurrentThumbId() {
        var el = document.getElementById('_thumbnail_id');
        if (!el) return null;
        var v = parseInt(el.value, 10);
        return isNaN(v) || v <= 0 ? null : v;
    }

    // Vælg tekst: caption -> description -> alt
    function chooseBestText(att) {
        if (!att) return '';

        var caption = (att.get('caption') || '').trim();
        var desc = (att.get('description') || '').trim();
        var alt = (att.get('alt') || '').trim();

        if (caption) return caption;
        if (desc) return desc;
        if (alt) return alt;
        return '';
    }

    function fillCaptionFromThumb(thumbId) {
        if (!window.wp || !wp.media || !thumbId) return;

        var input = getCaptionInput();
        if (!input) return;

        var attachment = wp.media.attachment(thumbId);
        
        // Hent data (hvis ikke allerede loaded)
        attachment.fetch().then(function () {
            var text = chooseBestText(attachment);
            input.value = text;

            // Evt. trigger input/change så ACF m.m. fanger ændringen
            var evt = new Event('input', { bubbles: true });
            input.dispatchEvent(evt);
        });

        return true;
    }

    function setupWatcher() {
        var thumbEl = document.getElementById('_thumbnail_id');
        if (!thumbEl) return;

        var lastId = getCurrentThumbId();

        // Tjek med jævne mellemrum om featured image er ændret
        setInterval(function () {
            var currentId = getCurrentThumbId();
            
            if (!currentId || currentId === lastId) return;

            lastId = currentId;
            if (fillCaptionFromThumb(currentId)) {
                showFeaturedCaptionNotice();
            }
        }, 500); // 0,8 sek – juster hvis du vil
    }

    // Vent et kort øjeblik til DOM og Gutenberg er klar
    function showFeaturedCaptionNotice() {
        // Forsøg at finde featured image-boksen
        var featuredBox =
            document.querySelector('#postimagediv') || // Classic editor / metabox
            document.querySelector('.editor-post-featured-image') || // Gutenberg center
            document.querySelector('.edit-post-featured-image__container') || // andre varianter
            document.querySelector('[data-panel-id="featured-image"]'); // Gutenberg sidebar panel

        if (!featuredBox) {
            return;
        }

        // Opret en tydelig boks
        var notice = document.createElement('div');
        notice.className = 'my-featured-caption-notice';
        notice.innerHTML = '<strong class="info">Billedtekst indsat automatisk</strong><br>' +
            'Teksten fra det valgte billede er nu kopieret ind i <code>Billedtekst til udvalgt billede</code>. ' +
            'Du kan tilpasse den i feltet herunder, hvis du vil.';

        // Indsæt boksen lige før featured image-boksen
        featuredBox.parentNode.insertBefore(notice, featuredBox);

        // Tilføj highlight-ramme
        featuredBox.classList.add('my-featured-caption-highlight');

        // Fjern highlight igen efter et par sekunder
        setTimeout(function () {
            jQuery('.my-featured-caption-notice').fadeOut();
        }, 5000);
    }
    
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setupWatcher();
    } else {
        document.addEventListener('DOMContentLoaded', setupWatcher);
    }
})();
