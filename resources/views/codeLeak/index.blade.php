@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/codeLeak.css?v=') . VERSION }}">

    <script>
        Ext.onReady(function () {
            Ext.create('Ext.data.Store', {
                storeId: 'store',
                pageSize: 50,
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: '/api/codeLeak',
                    reader: {
                        rootProperty: 'data',
                        totalProperty: 'total',
                    }
                }
            });

            var GitHub = 'https://github.com/';

            var status = [
                {text: '未审', value: 0, color: 'gray'},
                {text: '误报', value: 1, color: 'blue'},
                {text: '异常', value: 2, color: 'red'},
                {text: '解决', value: 3, color: 'green'},
            ];

            var tplStatus = new Ext.XTemplate('<div class="tag tag-{color}">{text}</div>');

            var grid = Ext.create('plugin.grid', {
                store: Ext.data.StoreManager.lookup('store'),
                bufferedRenderer: false,
                selType: 'checkboxmodel',
                tbar: {
                    margin: '5 12 15 18',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'hbox',
                            defaults: {
                                margin: '0 15 0 0',
                                width: 100,
                            },
                            items: [
                                {
                                    xtype: 'datefield',
                                    name: 'sdate',
                                    format: 'Y-m-d',
                                    maxValue: new Date(),
                                    emptyText: '开始日期',
                                    width: 120,
                                },
                                {
                                    xtype: 'datefield',
                                    name: 'edate',
                                    format: 'Y-m-d',
                                    maxValue: new Date(),
                                    emptyText: '结束日期',
                                    width: 120,
                                },
                                {
                                    xtype: 'combo',
                                    valueField: 'value',
                                    name: 'status',
                                    emptyText: '状态',
                                    store: {data: status}
                                },
                                {
                                    xtype: 'textfield',
                                    name: 'repo_owner',
                                    emptyText: '用户名',
                                },
                                {
                                    xtype: 'textfield',
                                    name: 'repo_name',
                                    emptyText: '仓库名',
                                },
                                {
                                    xtype: 'textfield',
                                    name: 'path',
                                    emptyText: '文件路径',
                                    width: 150,
                                },
                                {
                                    xtype: 'textfield',
                                    name: 'keyword',
                                    emptyText: '匹配关键字',
                                    width: 150,
                                },
                                {
                                    xtype: 'buttongroup',
                                    baseCls: 'border:0',
                                    width: 150,
                                    items: [
                                        {
                                            text: '查询',
                                            iconCls: 'icon-zoom',
                                            margin: '0 15 0 0',
                                            handler: function () {
                                                grid.down('pagingtoolbar').moveFirst();
                                                grid.store.getProxy().extraParams = this.up('form').getValues();
                                                grid.store.load();
                                            }
                                        },
                                        {
                                            text: '重置',
                                            iconCls: 'icon-page-wrench',
                                            handler: function () {
                                                this.up('form').reset();
                                            }
                                        }
                                    ]
                                }
                            ]
                        },
                        '->',
                        {
                            text: '批量操作',
                            margin: '0 13 0 0',
                            iconCls: 'icon-page-edit',
                            menu: {
                                items: [
                                    {
                                        text: '设为未审',
                                        iconCls: 'icon-bullet-gray',
                                        handler: function () {
                                            batchOpWithConfirm('PUT', 'batchUpdate', {status: 0});
                                        }
                                    },
                                    {
                                        text: '设为误报',
                                        iconCls: 'icon-bullet-blue',
                                        handler: function () {
                                            batchOpWithConfirm('PUT', 'batchUpdate', {status: 1});
                                        }
                                    },
                                    {
                                        text: '设为异常',
                                        iconCls: 'icon-bullet-red',
                                        handler: function () {
                                            batchOpWithConfirm('PUT', 'batchUpdate', {status: 2});
                                        }
                                    },
                                    {
                                        text: '设为解决',
                                        iconCls: 'icon-bullet-green',
                                        handler: function () {
                                            batchOpWithConfirm('PUT', 'batchUpdate', {status: 3});
                                        }
                                    },
                                    '-',
                                    {
                                        text: '编辑信息',
                                        iconCls: 'icon-page-wrench',
                                        handler: function () {
                                            var win = winForm('', function () {
                                                var params = this.up('form').getValues();
                                                batchOp('PUT', 'batchUpdate', params);
                                                win.close();
                                            }, true)
                                        }
                                    },
                                    {
                                        iconCls: 'icon-cross',
                                        text: '删除记录',
                                        handler: function () {
                                            batchOpWithConfirm('DELETE', 'batchDestroy', {});
                                        }
                                    }
                                ]
                            }
                        }
                    ]
                },
                columns: [
                    {
                        text: 'ID',
                        dataIndex: 'id',
                        width: 75,
                        align: 'center',
                        hidden: true,
                    },
                    {
                        text: '扫描时间',
                        dataIndex: 'created_at',
                        width: 110,
                        align: 'center',
                        renderer: function (value) {
                            return value.replace(' ', '<br/>');
                        }
                    },
                    {
                        text: '状态',
                        dataIndex: 'status',
                        align: 'center',
                        width: 100,
                        renderer: function (value) {
                            return tplStatus.apply(status[value]);
                        }
                    },
                    {
                        text: '用户名',
                        dataIndex: 'repo_owner',
                        width: 150,
                        align: 'center',
                        renderer: function (value, cellmeta, record) {
                            var url = GitHub + record.get('repo_owner');
                            return '<a href="javascript:tool.winOpen(\'' + url + '\')">' + value + '</a>';
                        }
                    },
                    {
                        text: '仓库名',
                        dataIndex: 'repo_name',
                        width: 150,
                        align: 'center',
                        renderer: function (value, cellmeta, record) {
                            var url = GitHub + record.get('repo_owner') + '/' + record.get('repo_name');
                            return '<a href="javascript:tool.winOpen(\'' + url + '\')">' + value + '</a>';
                        }
                    },
                    {
                        text: '文件路径',
                        dataIndex: 'path',
                        flex: 1,
                        align: 'center',
                        renderer: function (value, cellmeta, record) {
                            var url = GitHub + record.get('repo_owner') + '/' + record.get('repo_name');
                            url += '/blob/' + record.get('html_url_blob') + '/' + record.get('path');
                            return '<a href="javascript:tool.winOpen(\'' + url + '\')">' + value + '</a>';
                        }
                    },
                    {
                        text: '仓库描述',
                        dataIndex: 'repo_description',
                        flex: 1,
                        align: 'center',
                        renderer: function (value) {
                            return value ? value : '-';
                        }
                    },
                    {
                        text: '匹配关键字',
                        dataIndex: 'keyword',
                        flex: 1,
                        align: 'center',
                    },
                    {
                        text: '说明',
                        dataIndex: 'description',
                        flex: 1,
                        align: 'center',
                        renderer: function (value) {
                            return value ? value : '-';
                        }
                    },
                    {
                        text: '操作',
                        sortable: false,
                        width: 220,
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
                                    text: '操作',
                                    iconCls: 'icon-bullet-green',
                                    menu: {
                                        items: [
                                            {
                                                text: '设为未审',
                                                iconCls: 'icon-bullet-gray',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    update(record, 'status', 0);
                                                }
                                            },
                                            {
                                                text: '设为误报',
                                                iconCls: 'icon-bullet-blue',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    update(record, 'status', 1);
                                                }
                                            },
                                            {
                                                text: '设为异常',
                                                iconCls: 'icon-bullet-red',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    update(record, 'status', 2);
                                                }
                                            },
                                            {
                                                text: '设为解决',
                                                iconCls: 'icon-bullet-green',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    update(record, 'status', 3);
                                                }
                                            },
                                            '-',
                                            {
                                                text: '编辑信息',
                                                iconCls: 'icon-page-wrench',
                                                handler: function (obj) {
                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    var win = winForm(record.data.description, function () {
                                                        var params = this.up('form').getValues();
                                                        update(record, 'description', params.description);
                                                        win.close();
                                                    }, false)
                                                }
                                            },
                                            {
                                                text: '删除记录',
                                                iconCls: 'icon-cross',
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
                                                            var record = obj.up('buttongroup').getWidgetRecord();
                                                            var url = '/api/codeLeak/' + record.id;
                                                            tool.ajax('DELETE', url, {uuid: record.data.uuid}, function (rsp) {
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
                                },
                                {
                                    text: '更多',
                                    iconCls: 'icon-bullet-orange',
                                    margin: '0 0 0 10',
                                    menu: {
                                        items: [
                                            {
                                                text: '代码快照',
                                                iconCls: 'icon-folder-page',
                                                handler: function (obj) {
                                                    var winFragment = Ext.create('Ext.window.Window', {
                                                        title: '代码快照',
                                                        iconCls: 'icon-folder-page',
                                                        width: 1200,
                                                        height: 600,
                                                        bodyPadding: 15,
                                                        overflowY: 'auto',
                                                        html: '查 询 中 . .',
                                                    }).show().removeCls('x-unselectable');

                                                    var record = obj.up('buttongroup').getWidgetRecord();
                                                    tool.ajax('GET', '/api/codeFragment', {uuid: record.data.uuid}, function (rsp) {
                                                        if (!rsp.success) {
                                                            winFragment.setHtml(rsp.message);
                                                            return;
                                                        }

                                                        var content = '';
                                                        Ext.each(rsp.data, function (item) {
                                                            content += '<code>' + Ext.String.htmlEncode(item.content) + '</code>';
                                                        })
                                                        winFragment.setHtml('<pre class="code-fragment">' + content + '</pre>');
                                                    });
                                                }
                                            },
                                            {
                                                text: '加入白名单',
                                                iconCls: 'icon-add',
                                                handler: function (obj) {
                                                    Ext.Msg.show({
                                                        title: '提示',
                                                        iconCls: 'icon-page-star',
                                                        message: '确定将此仓库加入白名单？<br/>加入后扫描任务将自动忽略此仓库！',
                                                        buttons: Ext.Msg.YESNO,
                                                        fn: function (btn) {
                                                            if (btn !== 'yes') {
                                                                return;
                                                            }
                                                            var data = obj.up('buttongroup').getWidgetRecord().data;
                                                            var params = {value: data.repo_owner + '/' + data.repo_name};
                                                            tool.ajax('POST', '/api/configWhitelist', params, function (rsp) {
                                                                if (rsp.success) {
                                                                    tool.toast('操作成功！', 'success');
                                                                } else {
                                                                    tool.toast('操作失败！<br/>可能此仓库已在白名单中..', 'error');
                                                                }
                                                            });
                                                        }
                                                    });
                                                }
                                            }
                                        ]
                                    }
                                }
                            ]
                        }
                    }
                ]
            });

            // 编辑信息
            function winForm(value, handler, modal) {
                return Ext.create('Ext.window.Window', {
                    title: '编辑信息',
                    iconCls: 'icon-add',
                    width: 350,
                    modal: modal,
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'form',
                            bodyPadding: 15,
                            items: [
                                {
                                    fieldLabel: '说明',
                                    name: 'description',
                                    xtype: 'textfield',
                                    value: value,
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
                                    handler: handler
                                }
                            ]
                        }
                    ]
                }).show();
            }

            // 更新数据
            function update(record, field, value) {
                var params = {};
                params[field] = value;
                tool.ajax('PUT', '/api/codeLeak/' + record.id, params, function (rsp) {
                    if (rsp.success) {
                        tool.toast('操作成功！', 'success');
                        record.set(field, rsp.data[field]);
                        record.dirty = false;
                        record.commit();
                    } else {
                        tool.toast(rsp.message, 'error');
                    }
                });
            }

            // 批量操作（确认执行）
            function batchOpWithConfirm(method, route, params) {
                Ext.Msg.show({
                    title: '提示',
                    iconCls: 'icon-page',
                    message: '确定执行此操作？',
                    buttons: Ext.Msg.YESNO,
                    fn: function (btn) {
                        if (btn !== 'yes') {
                            return;
                        }
                        batchOp(method, route, params);
                    }
                });
            }

            // 批量操作
            function batchOp(method, route, params) {
                var records = grid.getSelectionModel().getSelection();
                if (!records.length) {
                    tool.toast('请先勾选记录！');
                    return;
                }

                var uuid = [];
                for (var record of records) {
                    uuid.push(record.get('uuid'));
                }

                params.uuid = Ext.encode(uuid);
                tool.ajax(method, '/api/codeLeak/' + route, params, function (rsp) {
                    if (rsp.success) {
                        tool.toast('操作成功！', 'success');
                        grid.store.reload();
                        grid.getSelectionModel().clearSelections();
                    } else {
                        tool.toast(rsp.message, 'error');
                    }
                });
            }

            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'fit',
                items: [grid],
            });
        })
    </script>
@endsection
