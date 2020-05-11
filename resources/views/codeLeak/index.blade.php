@extends('base')
@section('content')
    <style>
        a, p, code, .x-grid-cell-inner, .hl{ font-family:Consolas, sans-serif !important;font-size:12px;white-space:pre-wrap;word-break:break-all; }
    </style>
    <script>
        Ext.onReady(function () {
            Ext.create('Ext.data.Store', {
                storeId: 'store',
                pageSize: 10,
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: '/api/codeLeak',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'total',
                    },
                }
            });

            var storeStatus = Ext.create('Ext.data.Store', {
                data: [
                    {text: '未审', value: 0},
                    {text: '误报', value: 1},
                    {text: '异常', value: 2},
                    {text: '解决', value: 3},
                ],
            });

            var grid = Ext.create('plugin.grid', {
                store: Ext.data.StoreManager.lookup('store'),
                bufferedRenderer: false,
                selType: 'checkboxmodel',
                region: 'center',
                tbar: [
                    {
                        xtype: 'form',
                        layout: 'hbox',
                        defaults: {margin: '0 10 10 0'},
                        items: [
                            {
                                xtype: 'combo',
                                displayField: 'keyword',
                                valueField: 'keyword',
                                name: 'keyword',
                                emptyText: '匹配关键词',
                                width: 110,
                                // TODO 从任务列表获取关键词
                            },
                            {
                                xtype: 'combo',
                                valueField: 'value',
                                name: 'status',
                                emptyText: '状态',
                                width: 70,
                                store: storeStatus,
                            },
                            {
                                xtype: 'textfield',
                                name: 'repo_owner',
                                fieldLabel: '',
                                emptyText: '仓库拥有者',
                                width: 100,
                            },
                            {
                                xtype: 'textfield',
                                name: 'repo_name',
                                fieldLabel: '',
                                emptyText: '仓库名',
                                width: 100,
                            },
                            {
                                xtype: 'textfield',
                                name: 'path',
                                width: 250,
                                emptyText: '文件地址',
                            },
                            {
                                xtype: 'datefield',
                                name: 'sdate',
                                format: 'Y-m-d',
                                width: 110,
                                maxValue: new Date(),
                                emptyText: '开始日期',
                            },
                            {
                                xtype: 'datefield',
                                name: 'edate',
                                format: 'Y-m-d',
                                width: 110,
                                maxValue: new Date(),
                                emptyText: '结束日期',
                            },
                            {
                                xtype: 'buttongroup',
                                baseCls: 'border:0',
                                width: 150,
                                items: [
                                    {
                                        text: '重 置',
                                        iconCls: 'icon-page-wrench',
                                        margin: '0 10 0 0',
                                        handler: function () {
                                            this.up('form').reset();
                                        },
                                    },
                                    {
                                        id: 'btnSearch',
                                        text: '查 询',
                                        iconCls: 'icon-zoom',
                                        handler: function () {
                                            grid.store.getProxy().extraParams = this.up('form').getValues();
                                            grid.store.load();
                                        },
                                    },
                                ],
                            },
                        ],
                    },
                    '->',
                    {
                        xtype: 'combo',
                        valueField: 'value',
                        emptyText: '批量操作',
                        width: 100,
                        store: storeStatus,
                        listeners: {
                            change: function (combo, records) {
                                setStatus(records);
                            },
                        },
                    },
                ],
                columns: [
                    {
                        text: 'ID',
                        dataIndex: 'id',
                        width: 60,
                        align: 'center',
                    },
                    {
                        text: '创建时间',
                        dataIndex: 'created_at',
                        width: 180,
                        align: 'center',
                    },
                    {
                        text: '状 态',
                        dataIndex: 'status',
                        width: 80,
                        align: 'center',
                        renderer: function (val) {
                            var record = storeFind(storeStatus, 'value', val);
                            var tplStatus = new Ext.XTemplate('<div>{text}</div>');
                            return tplStatus.apply({text: record.data.text, color: record.data.color});
                            //TODO 按钮样式
                        },
                    }, {
                        text: '文件哈希',
                        dataIndex: 'uuid',
                        width: 170,
                        align: 'center',
                    },
                    {
                        text: '仓库拥有者',
                        dataIndex: 'repo_owner',
                        width: 150,
                        align: 'center',
                    },
                    {
                        text: '仓库名',
                        dataIndex: 'repo_name',
                        width: 150,
                        align: 'center',
                    }, {
                        text: '文件地址',
                        dataIndex: 'path',
                        width: 150,
                    },
                    {
                        text: '仓库描述',
                        dataIndex: 'repo_description',
                        width: 200,
                    }, {
                        text: '匹配关键词',
                        dataIndex: 'keyword',
                        width: 100,
                        align: 'center',
                    },
                    {
                        text: '处理人',
                        dataIndex: 'handle_user',
                        width: 100,
                    },
                    {
                        text: '说 明',
                        dataIndex: 'description',
                        width: 340,
                    },
                    {
                        text: '操 作',
                        sortable: false,
                        width: 200,
                        xtype: 'widgetcolumn',
                        widget: {
                            xtype: 'buttongroup',
                            baseCls: 'border:0',
                            layout: {
                                type: 'hbox',
                                pack: 'center',
                            },
                            items: [
                                {
                                    text: '说 明',
                                    iconCls: 'icon-page-wrench',
                                    margin: '0 5',
                                    handler: function (obj) {
                                        var record = obj.up().getWidgetRecord();
                                        Ext.Msg.prompt('说明信息', '', function (btn, text) {
                                            if (btn !== 'ok') return;
                                            update(record, 'description', text);
                                        }, this, true, record.data.description);
                                    },
                                },
                                {
                                    text: '更多操作',
                                    menu: {
                                        items: [
                                            {
                                                text: '代码快照',
                                                iconCls: 'icon-page-wrench',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    tool.ajax('GET', '/api/codeFragment', {uuid: record.data.uuid}, function (rsp) {
                                                        Ext.Msg.show({
                                                            title: '代码快照',
                                                            iconCls: 'icon-code',
                                                            width: 1000,
                                                            maxWidth: 1000,
                                                            maxHeight: 800,
                                                            message: '<pre><code>' + rsp.content + '</code></pre>'
                                                        }).removeCls('x-unselectable');
                                                    });
                                                },
                                            },
                                            {
                                                text: '仓库主页',
                                                iconCls: 'icon-page-star',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    console.log(record);
                                                    winOpen('https://github.com/' + record.data.repo_owner + '/' + record.data.repo_name, 1300, 800);
                                                }
                                            },
                                            {
                                                text: '用户主页',
                                                iconCls: 'icon-page-lightning',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    winOpen('https://github.com/' + record.data.repo_owner, 1300, 800);
                                                }
                                            },
                                        ],
                                    }
                                }
                            ],
                        },
                    },
                ],
            });

            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'border',
                items: [grid],
            });

            var setStatus = function (status) {
                var records = grid.getSelectionModel().getSelection();
                if (!records.length) {
                    tool.toast('请勾选记录');
                    return false;
                }
                Ext.each(records, function (record) {
                    update(record, 'status', status);
                });
                tool.toast('设置完毕', 'success');
            };

            var update = function (record, field, value) {
                var params = {
                    [field]: value,
                };
                tool.ajax('put', '/api/codeLeak/' + record.data.id, params, function (rsp) {
                    if (rsp.success) {
                        store.reload();
                    } else {
                        tool.toast('设置失败', 'error');
                    }
                });
            };

            var storeFind = function (store, fieldName, value) {
                var exactMatch = Ext.isDefined(arguments[3]) ? arguments[3] : true;
                return store.findRecord(fieldName, value, 0, false, false, exactMatch);
            };

            var winOpen = function (url, width, height) {
                var screenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
                var screenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
                var w = screen.width;
                var h = screen.height;
                var top = (h - height) / 2 + screenTop;
                var left = (w - width) / 2 + screenLeft;
                var newWindow = window.open(url, '', 'width=' + width + ', height=' + height + ', top=' + top + ', left=' + left);
                if (window.focus) newWindow.focus();
            };
        })
    </script>
@endsection
