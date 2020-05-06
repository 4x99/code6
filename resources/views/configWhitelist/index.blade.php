@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            var store = Ext.create('Ext.data.Store', {
                autoLoad: true,
                pageSize: 100,
                proxy: {
                    type: 'ajax',
                    url: '/api/configWhitelist',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'total'
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
                title: null,
                iconCls: null,
                tools: null,
                store: store,
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
                        text: '拥有者',
                        dataIndex: 'value',
                        flex: 1,
                        renderer: function (val) {
                            return val.split('/')[0];
                        }
                    },
                    {
                        text: '仓库名',
                        dataIndex: 'value',
                        flex: 1,
                        renderer: function (val) {
                            return val.split('/')[1];
                        }
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
                                                    tool.ajax('DELETE', '/api/configWhitelist/' + record.id, {}, function (data) {
                                                        if (data.success) {
                                                            tool.toast('提交成功！', 'success');
                                                            grid.store.remove(record);
                                                        } else {
                                                            tool.error('提交失败！', 'warning');
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
                    title: '新增白名单',
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
                                    name: 'repo_owner',
                                    fieldLabel: '拥有者',
                                },
                                {
                                    name: 'repo_name',
                                    fieldLabel: '仓库名',
                                }
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
                                        var values = this.up('form').getValues();
                                        var params = {};
                                        params['value'] = values['repo_owner'] + '/' + values['repo_name'];
                                        tool.ajax('POST', '/api/configWhitelist', params, function (data) {
                                                if (data.success) {
                                                    win.close();
                                                    tool.toast('提交成功！', 'success');
                                                    var index = grid.store.indexOfId(data.data.id);
                                                    grid.store.insert(Math.max(0, index), data.data);
                                                } else {
                                                    tool.error('提交失败！', 'warning');
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
