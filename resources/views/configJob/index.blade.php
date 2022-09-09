@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/configJob.css?v=') . VERSION }}">

    <script>
        Ext.onReady(function () {
            Ext.QuickTips.init(true, {dismissDelay: 0});

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
                {value: 0, text: '记录文件的每个版本', qtip: '即文件每次提交（包含关键字）都会产生一条新的未审记录'},
                {value: 1, text: '一个文件只记录一次', qtip: '一个文件只记录一次'},
                {value: 2, text: '一个仓库只记录一次', qtip: '一个仓库只记录一次'},
            ];

            var grid = Ext.create('plugin.grid', {
                store: Ext.data.StoreManager.lookup('store'),
                bufferedRenderer: false,
                selType: 'checkboxmodel',
                tbar: {
                    margin: '5 12 15 18',
                    items: [
                        {
                            text: '关键字说明',
                            iconCls: 'icon-page-star',
                            handler: winHelp,
                        },
                        '->',
                        {
                            text: '批量删除',
                            iconCls: 'icon-cross',
                            handler: function () {
                                var records = grid.getSelectionModel().getSelection();
                                if (!records.length) {
                                    tool.toast('请先勾选任务！');
                                    return;
                                }

                                Ext.Msg.show({
                                    title: '提示',
                                    iconCls: 'icon-page',
                                    message: '确定执行此操作？',
                                    buttons: Ext.Msg.YESNO,
                                    fn: function (btn) {
                                        if (btn !== 'yes') {
                                            return;
                                        }


                                        var id = [];
                                        for (var record of records) {
                                            id.push(record.get('id'));
                                        }

                                        var params = {id: Ext.encode(id)};
                                        tool.ajax('DELETE', '/api/configJob/batchDestroy', params, function (rsp) {
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
                        '-',
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
                        renderer: function (value) {
                            return Ext.String.htmlEncode(value);
                        }
                    },
                    {
                        text: '扫描页数',
                        dataIndex: 'scan_page',
                        width: 110,
                        align: 'center',
                    },
                    {
                        text: '扫描间隔',
                        dataIndex: 'scan_interval_min',
                        width: 110,
                        align: 'center',
                        renderer: function (value) {
                            return value + ' 分钟';
                        }
                    },
                    {
                        text: '扫描结果',
                        dataIndex: 'store_type',
                        width: 160,
                        align: 'center',
                        renderer: function (value) {
                            return storeType[value].text;
                        }
                    },
                    {
                        text: '下次扫描时间',
                        dataIndex: 'next_scan_at',
                        width: 160,
                        align: 'center',
                        renderer: function (value) {
                            return value ? value : '-';
                        }
                    },
                    {
                        text: '最近扫描时间',
                        dataIndex: 'last_scan_at',
                        width: 160,
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
                            return value ? Ext.String.htmlEncode(value) : '-';
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
                            baseCls: '',
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
                                        winForm(record.data);
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
                                    xtype: data.id ? 'textfield' : 'textareafield',
                                    name: 'keyword',
                                    fieldLabel: '扫描关键字',
                                    value: data.keyword,
                                    emptyText: data.id ? '' : '支持批量添加（一行一个关键字）',
                                },
                                {
                                    xtype: 'numberfield',
                                    name: 'scan_page',
                                    fieldLabel: '扫描页数（每页 30 条）',
                                    minValue: 1,
                                    allowDecimals: false,
                                    value: data.scan_page ? data.scan_page : 3,
                                },
                                {
                                    xtype: 'numberfield',
                                    name: 'scan_interval_min',
                                    fieldLabel: '扫描间隔（分钟）',
                                    minValue: 1,
                                    allowDecimals: false,
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
                                            '</tpl>',
                                        ]
                                    }
                                },
                                {
                                    name: 'description',
                                    fieldLabel: '说　　明',
                                    allowBlank: true,
                                    value: data.description,
                                    emptyText: '备注信息（选填）..',
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
                                                if (data.id) {
                                                    tool.toast('操作成功', 'success');
                                                    var index = data.id ? grid.store.indexOfId(data.id) : 0;
                                                    grid.store.insert(Math.max(0, index), rsp.data);
                                                } else {
                                                    grid.store.reload();
                                                    tool.toast(rsp.message, 'success');
                                                }
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

            function winHelp() {
                var content = '<div class="help">';
                content += '<p>1. 精确匹配：<span>keyword</span></p>';
                content += '<p>2. 关键字有空格或其他符号（引号）：<span>"hello world"</span></p>';
                content += '<p>3. 匹配多个关键字（AND）：<span>mysql AND password</span>（同时匹配 mysql 和 password）</p>';
                content += '<p>4. 排除特定关键字（NOT）：<span>mysql NOT localhost</span>（匹配 mysql 但不含 localhost）</p>';
                content += '<p>5. 匹配指定语言类型（language）：<span>mysql language:go</span>（匹配含 mysql 的 go 文件）</p>';
                content += '<p>6. 扫描时 GitHub 会忽略以下符号：<span>@ . , : ; / \\ ` \' " = * ! ? # $ & + ^ | ~ < > ( ) { } [ ]</span></pre>';
                content += '<p>7. 可通过 <span>https://github.com/search?o=desc&s=indexed&type=code&q=关键字</span> 预览扫描结果</pre>';
                content += '</div>';

                Ext.Msg.show({
                    title: '关键字设置说明',
                    maxWidth: 800,
                    maxHeight: 600,
                    modal: false,
                    iconCls: 'icon-page-star',
                    message: content,
                }).removeCls('x-unselectable');
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
