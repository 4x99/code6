/**
 * 导出数据
 */
Ext.define('plugin.export', {
    extend: 'Ext.Button',
    text: '导出数据',
    iconCls: 'icon-excel',
    onClick: function () {
        var data = this.getData();
        this.saveCsv(data);
    },
    // 读取数据
    getData: function () {
        var header = [], value = [];
        var grid = this.up('gridpanel');
        var store = grid.store;
        var columns = grid.columns ? grid.columns : grid.columnManager.columns;
        Ext.each(store.getData().items, function (record, index) {
            var row = [];
            if (store.filters && !store.filters.filterFn(record)) {
                return true;
            }
            Ext.each(columns, function (column) {
                if (column.hidden || column.xtype === 'widgetcolumn') {
                    return true;
                }
                if (index === 0) {
                    header.push(column.text);
                }
                var value = record.get(column.dataIndex);
                if (column.renderer) {
                    value = column.renderer(value, {}, record);
                }
                value = String(value).replace(/\n+|(<br\s*\/?>)+/ig, ' '); // 去除换行
                value = Ext.util.Format.stripTags(value);
                row.push(`"${value}"`);
            });
            value.push(row.join(','));
        });
        return header.join(',') + '\n' + value.join('\n');
    },
    // 保存 CSV 文件
    saveCsv: function (data) {
        const blob = new Blob([data], {type: 'text/csv'});
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url;
        a.download = Ext.Date.format(new Date(), 'YmdHis') + '.csv'
        a.click()
        a.remove();
    }
});
