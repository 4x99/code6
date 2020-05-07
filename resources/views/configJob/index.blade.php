@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            var store = Ext.create('Ext.data.Store', {
                autoLoad: true,
                autoSync: true,
                pageSize: 10,
                proxy: {
                    type: 'ajax',
                    url: '/api/configJob',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'meta.total'
                    }
                }
            });

            var btnDel = Ext.create('Ext.button.Button', {
                text: '删除',
                iconCls: 'icon-cross',
                handler: function () {
                    var grid = this.up('gridpanel');
                    var selection = grid.selection;
                    if (selection) {
                        Ext.Msg.show({
                            title: '警告',
                            iconCls: 'icon-warning',
                            message: '确定删除该行？',
                            buttons: Ext.Msg.YESNO,
                            icon: Ext.Msg.QUESTION,
                            fn: function (btn) {
                                if (btn === 'yes') {
                                    tool.ajax('DELETE', '/api/configJob/' + selection.id, {}, function (data) {
                                        if (data.success) {
                                            tool.toast('删除成功！', 'success');
                                            grid.store.reload();
                                        } else {
                                            tool.toast(data.message ?? '', 'error');
                                        }
                                    });
                                }
                            }
                        });
                    }
                },
            });

            var btnAdd = Ext.create('Ext.button.Button', {
                text: '新增',
                iconCls: 'icon-add',
                margin: '0 20 0 0',
                handler: function () {
                    Ext.create('Ext.window.Window', {
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
                                        handler: function (btn) {
                                            var params = this.up('form').getValues();
                                            tool.ajax('POST', '/api/configJob', params, function (data) {
                                                    if (data.success) {
                                                        btn.up('window').close();
                                                        tool.toast('新增成功！', 'success');
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
                    }).show()
                }
            });

            var rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
                pluginId: 'plnRowEditing',
                errorSummary: false, //右侧提示
                saveBtnText: '保存',
                cancelBtnText: '取消',
                listeners: {
                    edit: function (editor, e) {
                        var record = e.newValues;
                        var params = {
                            'keyword': record.keyword,
                            'scan_page': record.scan_page,
                            'scan_interval_min': record.scan_interval_min,
                            'description': record.description,
                        };
                        tool.ajax('PUT', '/api/configJob/' + record.id, params, function (data) {
                            if (data.success) {
                                tool.toast('修改成功！', 'success');
                                grid.store.reload();
                            } else {
                                tool.toast(data.message ?? '', 'error');
                            }
                        });
                    }
                }
            });

            var grid = Ext.create('plugin.grid', {
                store: store,
                viewConfig: {
                    stripeRows: false,
                },
                tbar: [
                    '->',
                    btnDel,
                    btnAdd,
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
                        completeOnEnter: false,
                        editor: {
                            xtype: 'textfield',
                            allowBlank: false,
                        }
                    },
                    {
                        text: '扫描页码',
                        dataIndex: 'scan_page',
                        flex: 1,
                        completeOnEnter: false,
                        editor: {
                            xtype: 'textfield',
                            allowBlank: false,
                        }
                    },
                    {
                        text: '扫描间隔',
                        dataIndex: 'scan_interval_min',
                        flex: 1,
                        completeOnEnter: false,
                        editor: {
                            xtype: 'textfield',
                            allowBlank: false,
                        }
                    },
                    {
                        text: '描述',
                        dataIndex: 'description',
                        flex: 1,
                        completeOnEnter: false,
                        editor: {
                            xtype: 'textfield',
                            allowBlank: false,
                        }
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
                ],
                plugins: [
                    rowEditing,
                ],
            });

            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'fit',
                items: [grid],
            });

        });
    </script>
@endsection
