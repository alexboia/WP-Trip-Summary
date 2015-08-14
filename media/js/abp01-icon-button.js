(function() {
    L.Control.IconButton = L.Control.extend({
        _targetUrl: null,
        _iconCssClass: null,

        options: {
            position: 'topleft',
            openInNewWindow: false
        },

        initialize: function(iconCssClass, targetUrl, options) {
            this._iconCssClass = iconCssClass || 'abp01-none';
            this._targetUrl = targetUrl || 'javascript:void(0)';
            L.Util.setOptions(this, options || {});
        },

        onAdd: function(map) {
            var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control abp01-leaflet-icon-button-container');            
            var buttonLink = L.DomUtil.create('a', 'abp01-leaflet-icon-button-link', container);

            buttonLink.href = this._targetUrl;
            if (this.options.openInNewWindow) {
                buttonLink.target = '_blank';
            }

            L.DomUtil.create('span', this._iconCssClass, buttonLink);
            return container;
        }
    });
})();