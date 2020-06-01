Ext.define('plugin.toast', {
    extend: 'Ext.Component',
    delay: 5000, // 显示时间
    initComponent: function () {
        this.createDiv();
        this.callParent();
    },
    createDiv: function () {
        if (!this.div) {
            this.div = Ext.DomHelper.insertFirst(document.body, {}, true);
            this.div.applyStyles({
                position: 'fixed',
                right: '20px',
                bottom: '20px',
                width: '300px',
                zIndex: 99999,
                fontSize: '14px',
                lineHeight: '25px',
                letterSpacing: '1px'
            });
        }
    },
    show: function (text, iconCls) {
        var msg = Ext.DomHelper.append(this.div, '<div class="toast-msg">' + text + '</div>', true);
        msg.applyStyles({
            marginTop: '5px',
            padding: '10px 20px',
            borderRadius: '5px',
            boxShadow: '2px 2px 2px #CCC',
            border: '1px solid #B5B5B5',
            background: '#FFF',
            color: '#666',
            wordWrap: 'break-word'
        });
        var icon = Ext.DomHelper.insertFirst(msg, '<div></div>', true);
        icon.addCls('x-title-icon ' + iconCls);
        icon.applyStyles({
            width: '16px',
            height: '16px',
            margin: '-2px 8px 0 0'
        });
        msg.ghost('t', {delay: this.delay, remove: true});
    },
    // 普通消息
    info: function (text) {
        this.show(text, 'icon-page');
    },
    // 成功消息
    success: function (text) {
        this.show(text, 'icon-tick');
    },
    // 错误消息
    error: function (text) {
        this.show(text, 'icon-cross');
    },
    // 警告消息
    warning: function (text) {
        this.show(text, 'icon-warning');
    }
});
