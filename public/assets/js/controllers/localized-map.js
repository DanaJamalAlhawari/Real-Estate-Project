
(function () {
    'use strict';

    const STYLE_URL =
        'https://tiles.openfreemap.org/styles/liberty';

    function getLocale() {
        return document.documentElement.lang
            .toLowerCase()
            .startsWith('ar') ? 'ar' : 'en';
    }

    function getLabelExpression(locale) {
        if (locale === 'ar') {
            return [
                'coalesce',
                ['get', 'name:ar'],
                ['get', 'name_ar'],
                ['get', 'name'],
                ['get', 'name:nonlatin'],
                ['get', 'name_en'],
                ''
            ];
        }

        return [
            'coalesce',
            ['get', 'name:en'],
            ['get', 'name_en'],
            ['get', 'name:latin'],
            ['get', 'name'],
            ''
        ];
    }

    function localizeLabels(map, locale = getLocale()) {
        const layers = map.getStyle()?.layers || [];

        layers.forEach(layer => {
            if (layer.type !== 'symbol') return;

            const textField = layer.layout?.['text-field'];

            if (!textField) return;

            const expression = JSON.stringify(textField);

            // Only translate geographic names.
            // Preserve road numbers and other labels.
            if (!expression.includes('name')) return;

            map.setLayoutProperty(
                layer.id,
                'text-field',
                getLabelExpression(locale)
            );
        });
    }

    function createMap(container, options = {}) {
        if (typeof maplibregl === 'undefined') {
            console.error('MapLibre GL JS is not loaded.');
            return null;
        }

        const map = new maplibregl.Map({
            container: container,
            style: STYLE_URL,
            center: [35.9106, 31.9539],
            zoom: 11,
            ...options
        });

        map.on('style.load', () => {
            localizeLabels(map);
        });

        map.addControl(
            new maplibregl.NavigationControl({
                showCompass: false
            }),
            'top-right'
        );

        return map;
    }

    function createMarker(map, latitude, longitude) {
        return new maplibregl.Marker({
            color: '#2563eb'
        })
            .setLngLat([longitude, latitude])
            .addTo(map);
    }

    window.MakanMap = {
        createMap,
        createMarker,
        localizeLabels,
        getLocale
    };
})();
