@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            Ext.define('configToken', {
                extend: 'Ext.data.Model',
                fields: ['token', 'api_limit', 'description'],
            });

            var store = Ext.create('Ext.data.Store', {
                model: 'configToken',
                proxy: {
                    type: 'ajax',
                    url: '/api/configToken',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'total',
                    }
                }
            });

            var grid = Ext.create('plugin.grid', {
                title: null,
                iconCls: null,
                tools: null,
                store: store,
                tbar: [
                    {
                        text: '新 增',
                        iconCls: 'icon-add',
                        margin: '0 20 0 0',
                        handler: winAdd,
                    },
                ],
                columns: [
                    {
                        text: '密钥',
                        dataIndex: 'token',
                        align: 'center',
                        flex: 1,
                        editor: {
                            field: {
                                xtype: 'textfield',
                                allowBlank: false,
                            },
                        },
                    },
                    {
                        text: '创建时间',
                        dataIndex: 'created_at',
                        align: 'center',
                        flex: 1,
                    },
                    {
                        text: '状态',
                        dataIndex: 'status',
                        width: 20,
                        flex: 1,
                        align: 'center',
                        renderer: function (val) {
                            return val === 0 ? '异常' : '正常';
                        },
                    },
                    {
                        text: '接口限制次数',
                        dataIndex: 'api_limit',
                        align: 'center',
                        flex: 1,
                        editor: {
                            field: {
                                xtype: 'textfield',
                                allowBlank: false,
                            },
                        },
                    }, {
                        text: '接口剩余次数',
                        dataIndex: 'api_remaining',
                        align: 'center',
                        flex: 1,
                    }, {
                        text: '接口限制重置时间',
                        dataIndex: 'api_reset_at',
                        align: 'center',
                        flex: 1,
                    },
                    {
                        text: '说明',
                        dataIndex: 'description',
                        align: 'center',
                        flex: 1,
                        editor: {
                            field: {
                                xtype: 'textfield',
                                allowBlank: false,
                            },
                        },
                    },
                    {
                        text: '操 作',
                        sortable: false,
                        width: 150,
                        align: 'center',
                        xtype: 'widgetcolumn',
                        widget: {
                            xtype: 'buttongroup',
                            baseCls: 'border:0',
                            items: [
                                {
                                    text: '编 辑',
                                    margin: '0 5',
                                    handler: function () {
                                        // TODO
                                    },
                                },
                                {
                                    text: '删 除',
                                    iconCls: 'icon-cross',
                                    margin: '0 5',
                                    handler: function (obj) {
                                        Ext.MessageBox.show({
                                            title: "提示",
                                            msg: "确定删除吗",
                                            buttons: Ext.Msg.YESNO,
                                            fn: function (btn) {
                                                if (btn === 'yes') {
                                                    var record = obj.up().getWidgetRecord();
                                                    tool.ajax('DELETE', '/api/configToken/' + record.id, {}, function (data) {
                                                        if (data.success) {
                                                            tool.toast('删除成功', 'success');
                                                            grid.store.remove(record);
                                                        } else {
                                                            tool.toast(data.message, 'warning');
                                                        }
                                                    });
                                                }
                                            },
                                        });
                                    },
                                }
                            ]
                        }
                    },
                ],
                selModel: 'cellmodel',
                plugins: {
                    ptype: 'rowediting',
                    clicksToEdit: 1,
                    errorSummary: false,
                    saveBtnText: '保存',
                    cancelBtnText: '取消',
                    listeners: {
                        edit: function (editor, e) {
                            var record = e.record.data,
                                params = {
                                    'token': record.token,
                                    'api_limit': record.api_limit,
                                    'description': record.description,
                                };
                            tool.ajax('PUT', '/api/configToken/' + record.id, params, function (data) {
                                    if (data.success) {
                                        tool.toast('更新成功', 'success');
                                        e.record.commit();
                                    } else {
                                        tool.toast(data.message, 'warning');
                                    }
                                }
                            );
                        },
                    },
                },
            });
            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'fit',
                items: [grid],
            });
            grid.store.load();

            function winAdd() {
                var win = Ext.create('Ext.window.Window', {
                    title: '新增令牌',
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
                                    name: 'token',
                                    fieldLabel: '令牌',
                                },
                                {
                                    name: 'description',
                                    fieldLabel: '说明',
                                },
                                {
                                    name: 'api_limit',
                                    fieldLabel: '接口限制次数',
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
                                        var values = this.up('form').getValues(),
                                            params = {
                                                token: values['token'],
                                                api_limit: values['api_limit'],
                                                description: values['description'],
                                            };
                                        tool.ajax('POST', '/api/configToken', params, function (data) {
                                                if (data.success) {
                                                    win.close();
                                                    tool.toast('新增成功', 'success');
                                                    var index = grid.store.indexOfId(data.data.id);
                                                    grid.store.insert(Math.max(0, index), data.data);
                                                } else {
                                                    tool.toast(data.message, 'warning');
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
