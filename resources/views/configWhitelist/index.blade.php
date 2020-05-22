@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            Ext.create('Ext.data.Store', {
                storeId: 'store',
                pageSize: 50,
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: '/api/configWhitelist',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'total',
                    }
                }
            });

            // 白名单列表
            var grid = Ext.create('plugin.grid', {
                store: Ext.data.StoreManager.lookup('store'),
                tbar: {
                    margin: '5 12 15 18',
                    items: [
                        {
                            xtype: 'tbtext',
                            html: '提示：扫描任务将自动忽略白名单内的仓库',
                        },
                        '->',
                        {
                            text: '新增白名单',
                            iconCls: 'icon-add',
                            margin: '0 13 0 0',
                            handler: winForm,
                        }
                    ]
                },
                columns: [
                    {
                        text: 'ID',
                        dataIndex: 'id',
                        width: 75,
                        align: 'center',
                    },
                    {
                        text: '用户名（仓库拥有者）',
                        dataIndex: 'value',
                        flex: 1,
                        align: 'center',
                        renderer: function (value) {
                            return value.split('/')[0];
                        }
                    },
                    {
                        text: '仓库名',
                        dataIndex: 'value',
                        flex: 1,
                        align: 'center',
                        renderer: function (value) {
                            return value.split('/')[1];
                        }
                    },
                    {
                        text: '操作',
                        sortable: false,
                        width: 250,
                        align: 'center',
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
                                    text: '访问',
                                    iconCls: 'icon-bullet-green',
                                    margin: '0 20 0 0',
                                    handler: function (obj) {
                                        var record = obj.up().getWidgetRecord();
                                        var url = 'https://github.com/';
                                        tool.winOpen(url + record.get('value'));
                                    }
                                },
                                {
                                    text: '删除',
                                    iconCls: 'icon-bullet-red',
                                    handler: function (obj) {
                                        Ext.Msg.show({
                                            title: '警告',
                                            iconCls: 'icon-warning',
                                            message: '确定删除此项？',
                                            buttons: Ext.Msg.YESNO,
                                            fn: function (btn) {
                                                if (btn !== 'yes') {
                                                    return;
                                                }
                                                var record = obj.up().getWidgetRecord();
                                                var url = '/api/configWhitelist/' + record.id;
                                                tool.ajax('DELETE', url, {}, function (rsp) {
                                                    if (rsp.success) {
                                                        tool.toast(rsp.message, 'success');
                                                        grid.store.remove(record);
                                                    } else {
                                                        tool.toast(rsp.message, 'error');
                                                    }
                                                });
                                            }
                                        });
                                    }
                                }
                            ]
                        }
                    }
                ],
            });

            function winForm() {
                var win = Ext.create('Ext.window.Window', {
                    title: '白名单信息',
                    iconCls: 'icon-page-wrench',
                    width: 350,
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'form',
                            bodyPadding: 15,
                            defaults: {
                                xtype: 'textfield',
                                allowBlank: false,
                            },
                            items: [
                                {
                                    name: 'repo_owner',
                                    fieldLabel: '用户名',
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
                                        tool.ajax('POST', '/api/configWhitelist', params, function (rsp) {
                                            if (rsp.success) {
                                                win.close();
                                                tool.toast('操作成功！', 'success');
                                                grid.store.insert(0, rsp.data);
                                            } else {
                                                tool.toast(rsp.message, 'error');
                                            }
                                        });
                                    }
                                }
                            ]
                        }
                    ]
                }).show();
            }

            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'fit',
                items: [grid],
            });
        });
    </script>
@endsection
