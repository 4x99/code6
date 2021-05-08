@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/configWhitelist.css?v=') . VERSION }}">

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
                selType: 'checkboxmodel',
                tbar: {
                    margin: '5 12 15 18',
                    items: [
                        {
                            xtype: 'tbtext',
                            html: '提示：扫描时将自动忽略白名单内的仓库',
                        },
                        '->',
                        {
                            text: '按文件名忽略',
                            iconCls: 'icon-folder-page',
                            margin: '0 13 0 0',
                            handler: winFormFile,
                        },
                        '-',
                        {
                            text: '批量删除',
                            margin: '0 13 0 5',
                            iconCls: 'icon-cross',
                            handler: function () {
                                Ext.Msg.show({
                                    title: '提示',
                                    iconCls: 'icon-page',
                                    message: '确定执行此操作？',
                                    buttons: Ext.Msg.YESNO,
                                    fn: function (btn) {
                                        if (btn !== 'yes') {
                                            return;
                                        }
                                        var records = grid.getSelectionModel().getSelection();
                                        if (!records.length) {
                                            tool.toast('请先勾选记录！');
                                            return;
                                        }
                                        var id = [];
                                        for (var record of records) {
                                            id.push(record.get('id'));
                                        }

                                        var params = {id: Ext.encode(id)};
                                        tool.ajax('DELETE', '/api/configWhitelist/batchDestroy', params, function (rsp) {
                                            if (rsp.success) {
                                                tool.toast('操作成功！', 'success');
                                                grid.store.reload();
                                                grid.getSelectionModel().clearSelections();
                                            } else {
                                                tool.toast(rsp.message, 'error');
                                            }
                                        });
                                    }
                                });
                            }
                        },
                        {
                            text: '新增仓库',
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
                        flex: 1,
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
                                    margin: '0 30 0 0',
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
                    title: '将仓库加入白名单',
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

            function winFormFile() {
                tool.ajax('GET', '/api/configWhitelistFile', {}, function (rsp) {
                    if (!rsp.success) {
                        tool.toast('读取配置错误！');
                        return false;
                    }

                    var winFile = Ext.create('Ext.window.Window', {
                        title: '按文件名忽略',
                        iconCls: 'icon-folder-page',
                        width: 500,
                        layout: 'fit',
                        items: [
                            {
                                xtype: 'form',
                                layout: 'form',
                                bodyPadding: '5 15 15 8',
                                tbar: [
                                    {
                                        xtype: 'tbtext',
                                        padding: '15 0 0 10',
                                        html: '<div class="tip">提示：一行一个，支持通配符，如 <b>nginx.conf</b>、<b>*.css</b></div>',
                                    }
                                ],
                                items: [
                                    {
                                        xtype: 'textareafield',
                                        name: 'value',
                                        value: rsp.data,
                                        fieldStyle: 'min-height:300px',
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
                                        text: '保存',
                                        formBind: true,
                                        handler: function () {
                                            var params = this.up('form').getValues();
                                            tool.ajax('POST', '/api/configWhitelistFile', params, function (rsp) {
                                                if (rsp.success) {
                                                    winFile.close();
                                                    tool.toast('保存成功！', 'success');
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
                });
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
