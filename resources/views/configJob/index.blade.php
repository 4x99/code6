@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            var store = Ext.create('Ext.data.Store', {
                autoLoad: true,
                pageSize: 10,
                proxy: {
                    type: 'ajax',
                    url: '/api/configJob',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'meta.total'
                    }
                },
                listeners: {
                    filterchange: function () {
                        this.totalCount = this.getCount();
                        this.down('pagingtoolbar').onLoad();
                    }
                }
            });

            var grid = Ext.create('plugin.grid', {
                title: '任务配置列表',
                store: store,
                iconCls: 'icon-grid',
                viewConfig: {
                    stripeRows: false
                },
                tbar: [
                    '->',
                    {
                        text: '新增',
                        iconCls: 'icon-add',
                        handler: winAdd
                    }
                ],
                columns: [
                    {
                        text: 'ID',
                        dataIndex: 'id',
                        width: 100,
                    },
                    {
                        text: '关键词',
                        dataIndex: 'keyword',
                        flex: 1,
                    },
                    {
                        text: '扫描页码',
                        dataIndex: 'scan_page',
                        flex: 1,
                    },
                    {
                        text: '扫描间隔',
                        dataIndex: 'scan_interval_min',
                        flex: 1,
                    },
                    {
                        text: '描述',
                        dataIndex: 'description',
                        flex: 1,
                    },
                    {
                        text: '最后扫描时间',
                        dataIndex: 'last_scan_at',
                        flex: 1,
                    },
                    {
                        text: '创建时间',
                        dataIndex: 'created_at',
                        flex: 1,
                    },
                    {
                        text: '更新时间',
                        dataIndex: 'updated_at',
                        flex: 1,
                    },
                    {
                        text: '操作',
                        sortable: false,
                        width: 80,
                        align: 'center',
                        xtype: 'widgetcolumn',
                        widget: {
                            xtype: 'buttongroup',
                            baseCls: 'border:0',
                            items: [
                                {
                                    text: '删除',
                                    iconCls: 'icon-cross',
                                    handler: function (obj) {
                                        Ext.Msg.show({
                                            title: '警告',
                                            iconCls: 'icon-warning',
                                            message: '确定删除该项？',
                                            buttons: Ext.Msg.YESNO,
                                            icon: Ext.Msg.QUESTION,
                                            fn: function (btn) {
                                                if (btn === 'yes') {
                                                    var record = obj.up().getWidgetRecord();
                                                    tool.ajax('DELETE', '/api/configJob/' + record.id, {}, function (data) {
                                                        if (data.success) {
                                                            tool.toast('提交成功！', 'success');
                                                            grid.store.remove(record);
                                                        } else {
                                                            tool.toast(data.message ?? '', 'error');
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }
                            ]
                        }
                    }
                ]
            });

            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'fit',
                items: [grid]
            });

            function winAdd() {
                var win = Ext.create('Ext.window.Window', {
                    title: '新增任务配置',
                    width: 300,
                    modal: true,
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'form',
                            bodyPadding: 10,
                            border: false,
                            defaults: {
                                xtype: 'textfield',
                                allowBlank: false,
                            },
                            items: [
                                {
                                    name: 'keyword',
                                    fieldLabel: '关键词',
                                },
                                {
                                    name: 'scan_page',
                                    fieldLabel: '扫描页码',
                                },
                                {
                                    name: 'scan_interval_min',
                                    fieldLabel: '扫描间隔',
                                },
                                {
                                    name: 'description',
                                    fieldLabel: '描述',
                                },
                            ],
                            buttons: [
                                {
                                    text: '重置',
                                    handler: function () {
                                        this.up('form').getForm().reset();
                                    }
                                },
                                {
                                    text: '提交',
                                    formBind: true,
                                    handler: function () {
                                        var params = this.up('form').getValues();
                                        tool.ajax('POST', '/api/configJob', params, function (data) {
                                                if (data.success) {
                                                    win.close();
                                                    tool.toast('提交成功！', 'success');
                                                    grid.store.reload();
                                                } else {
                                                    tool.toast(data.message ?? '', 'error');
                                                }
                                            }
                                        );
                                    }
                                }
                            ]
                        }
                    ]
                }).show();
            }
        });
    </script>
@endsection
