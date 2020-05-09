Ext.define('plugin.grid', {
    extend: 'Ext.grid.Panel',
    renderTo: Ext.getBody(),
    layout: 'fit',
    collapsible: false,
    columnLines: true,
    reserveScrollbar: true,
    height: 0,
    maxHeight: '100%',
    style: 'cursor:default',
    viewConfig: {
        enableTextSelection: true,
        emptyText: '<div style="text-align:center;padding:20px;color:#AAA">查 询 无 数 据</div>'
    },
    dockedItems: [
        {
            xtype: 'pagingtoolbar',
            dock: 'bottom',
            displayInfo: true,
        }
    ]
});
