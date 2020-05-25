Ext.Loader.setConfig({
    enabled: true,
    disableCaching: false,
    paths: {'plugin': '/js/extjs/plugin'}
});

// 资源版本号
Ext.Boot.Entry.prototype.getLoadUrl = function () {
    var url = Ext.Boot.canonicalUrl(this.url);
    var meta = Ext.select('meta[name=version]').first();
    var version = meta ? meta.getAttribute('content') : new Date().getTime();
    if (!this.loadUrl) {
        this.loadUrl = (url + (url.indexOf('?') === -1 ? '?' : '&') + 'v=' + version);
    }
    return this.loadUrl;
};

// 自适应宽度
Ext.EventManager.onWindowResize(function () {
    Ext.ComponentManager.each(function (id, cmp) {
        if (cmp.hasOwnProperty('renderTo') && cmp.updateLayout) {
            cmp.updateLayout();
        }
    });
});

// 常用工具
var tool = tool || {};

/**
 * 消息提示
 *
 * @param text 消息内容
 * @param type 消息类型 info、success、warning、error（默认：warning）
 * @param delay 显示时长（默认：5000 ms）
 */
tool.toast = function (text) {
    tool.toastInstance = tool.toastInstance || new Ext.create('plugin.toast');
    var type = arguments[1] ? arguments[1] : 'warning';
    tool.toastInstance.delay = arguments[2] ? arguments[2] : 5000;
    tool.toastInstance[type](text);
};

/**
 * Ajax 请求
 *
 * @param method
 * @param url
 * @param params
 * @param callback
 */
tool.ajax = function (method, url, params, callback) {
    Ext.Ajax.request({
        method: method,
        url: url,
        params: params,
        headers: {'X-XSRF-TOKEN': Ext.util.Cookies.get('XSRF-TOKEN')},
        success: function (rsp) {
            try {
                rsp = Ext.JSON.decode(rsp.responseText);
            } catch (e) {
                rsp = rsp.responseText;
            }
            callback(rsp);
        },
        failure: function (rsp) {
            tool.toast('Request failed ( code: ' + rsp.status + ' )');
        }
    });
};

/**
 * 打开新窗口（居中）
 *
 * @param url
 * @param width
 * @param height
 */
tool.winOpen = function (url, width, height) {
    width = width ? width : 1200;
    height = height ? height : 800;
    var left = (screen.width - width) / 2 + screenLeft;
    var top = (screen.height - height) / 2;
    window.open(url, '', 'width=' + width + ', height=' + height + ', left=' + left + ', top=' + top);
}
