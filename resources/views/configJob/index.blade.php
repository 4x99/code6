@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/configJob.css?v=') . VERSION }}">

    <script>
        Ext.onReady(function () {
            Ext.QuickTips.init(true, {dismissDelay: 0});

            var GitHub = 'https://github.com/';

            Ext.create('Ext.data.Store', {
                storeId: 'store',
                pageSize: 99999, // 不分页
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: '/api/configJob',
                }
            });

            var storeType = [
                {value: 0, text: '记录文件的每个版本', qtip: '即文件每次提交（包含关键字）会产生一条新的未审记录'},
                {value: 1, text: '一个文件只记录一次', qtip: '一个文件只记录一次'},
                {value: 2, text: '一个仓库只记录一次', qtip: '一个仓库只记录一次'},
            ];

            var grid = Ext.create('plugin.grid', {
                store: Ext.data.StoreManager.lookup('store'),
                tbar: {
                    margin: '5 12 15 18',
                    items: [
                        '->',
                        {
                            text: '新增任务',
                            iconCls: 'icon-add',
                            margin: '0 13 0 0',
                            handler: function () {
                                winForm([]);
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
                    },
                    {
                        text: '扫描关键字',
                        dataIndex: 'keyword',
                        flex: 1,
                        align: 'center',
                    },
                    {
                        text: '扫描页数',
                        dataIndex: 'scan_page',
                        width: 180,
                        align: 'center',
                    },
                    {
                        text: '扫描间隔（分钟）',
                        dataIndex: 'scan_interval_min',
                        width: 180,
                        align: 'center',
                    },
                    {
                        text: '扫描结果',
                        dataIndex: 'store_type',
                        width: 180,
                        align: 'center',
                        renderer: function (value) {
                            return storeType[value].text;
                        }
                    },
                    {
                        text: '最后扫描时间',
                        dataIndex: 'last_scan_at',
                        width: 180,
                        align: 'center',
                        renderer: function (value) {
                            return value ? value : '-';
                        }
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
                                    text: '编辑',
                                    iconCls: 'icon-bullet-green',
                                    margin: '0 20 0 0',
                                    handler: function (obj) {
                                        var record = obj.up().getWidgetRecord();
                                        winForm(record.data)
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
                                                var url = '/api/configJob/' + record.id;
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
                ]
            });

            function winForm(data) {
                var win = Ext.create('Ext.window.Window', {
                    title: '任务信息',
                    width: 600,
                    iconCls: 'icon-page-wrench',
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'form',
                            bodyPadding: 15,
                            defaults: {
                                xtype: 'textfield',
                                allowBlank: false,
                                labelAlign: 'right',
                            },
                            items: [
                                {
                                    name: 'keyword',
                                    fieldLabel: '扫描关键字',
                                    value: data.keyword,
                                },
                                {
                                    xtype: 'numberfield',
                                    name: 'scan_page',
                                    fieldLabel: '扫描页数（每页 30 条）',
                                    minValue: 1,
                                    value: data.scan_page ? data.scan_page : 3,
                                },
                                {
                                    xtype: 'numberfield',
                                    name: 'scan_interval_min',
                                    fieldLabel: '扫描间隔（分钟）',
                                    minValue: 1,
                                    value: data.scan_interval_min ? data.scan_interval_min : 60,
                                },
                                {
                                    xtype: 'combo',
                                    name: 'store_type',
                                    fieldLabel: '扫描结果',
                                    editable: false,
                                    valueField: 'value',
                                    store: {data: storeType},
                                    value: data.store_type ? data.store_type : 0,
                                    listConfig: {
                                        tpl: [
                                            '<tpl for=".">',
                                            '<li role="option" class="x-boundlist-item" data-qtip="{qtip}">{text}</li>',
                                            '</tpl>'
                                        ]
                                    }
                                },
                                {
                                    name: 'description',
                                    fieldLabel: '说　　明',
                                    allowBlank: true,
                                    value: data.description,
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
                                        var params = this.up('form').getValues();
                                        var method = data.id ? 'PUT' : 'POST';
                                        var url = data.id ? '/api/configJob/' + data.id : '/api/configJob';
                                        tool.ajax(method, url, params, function (rsp) {
                                            if (rsp.success) {
                                                win.close();
                                                tool.toast('操作成功', 'success');
                                                var index = data.id ? grid.store.indexOfId(data.id) : 0;
                                                grid.store.insert(Math.max(0, index), rsp.data);
                                            } else {
                                                tool.toast(rsp.message, 'warning');
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
