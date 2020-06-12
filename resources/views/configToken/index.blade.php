@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/configToken.css?v=') . VERSION }}">

    <script>
        Ext.onReady(function () {
            Ext.QuickTips.init(true, {dismissDelay: 0});

            Ext.create('Ext.data.Store', {
                storeId: 'store',
                pageSize: 99999, // 不分页
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: '/api/configToken',
                }
            });

            var content = '';
            content += '<p class="tip-title">1. 令牌是什么？<span></p>';
            content += '<p>用来请求 GitHub API 的 Token（即 GitHub personal access token）</p><br/>';
            content += '<p class="tip-title">2. 如何申请令牌？</p>';
            content += '<p>GitHub - Settings - Developer settings - Personal access tokens - Generate new token';
            content += '（<a target="_blank" href="https://github.com/settings/tokens/new">直达</a>）</p><br/>';
            content += '<p class="tip-title">3. 为何需要配置多个令牌？</p>';
            content += '<p>监控需要大量请求 GitHub API，而 GitHub 限制了 API 的请求速率';
            content += '（<a target="_blank" href="https://developer.github.com/v3/#rate-limiting">GitHub API v3 - Rate limiting</a>）</p>';
            content += '<p>因此需要多个 GitHub 账号创建令牌用于轮询请求（建议至少配置 5 个令牌）</p>';

            var grid = Ext.create('plugin.grid', {
                store: Ext.data.StoreManager.lookup('store'),
                tbar: {
                    margin: '5 12 15 18',
                    items: [
                        {
                            text: '帮助信息',
                            iconCls: 'icon-page-star',
                            handler: function () {
                                Ext.Msg.show({
                                    title: '帮助信息',
                                    iconCls: 'icon-page-star',
                                    modal: false,
                                    maxWidth: 800,
                                    message: content,
                                });
                            }
                        },
                        '->',
                        {
                            text: '新增令牌',
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
                        text: '令牌',
                        dataIndex: 'token',
                        width: 380,
                        align: 'center',
                    },
                    {
                        text: '状态',
                        tooltip: '每分钟更新',
                        dataIndex: 'status',
                        width: 150,
                        align: 'center',
                        xtype: 'booleancolumn',
                        trueText: '<div class="tag tag-green">正常</div>',
                        falseText: '<div class="tag tag-red">异常</div>',
                    },
                    {
                        text: '创建时间',
                        dataIndex: 'created_at',
                        align: 'center',
                        width: 180,
                        hidden: true,
                    },
                    {
                        text: 'GitHub接口请求配额',
                        tooltip: '每分钟更新',
                        columns: [
                            {
                                text: '接口用量',
                                tooltip: '已用次数 / 最大允许请求次数',
                                width: 180,
                                renderer: function (value, cellmeta, record) {
                                    var item = [], data = record.data;
                                    item.limit = data.api_limit;
                                    item.used = Math.max(0, item.limit - data.api_remaining);
                                    item.percent = parseFloat(item.used / item.limit * 100);
                                    return new Ext.XTemplate(
                                        '<div class="progress">',
                                        '    <div style="width:{percent}%">',
                                        '        <span>{used} / {limit}</span>',
                                        '    </div>',
                                        '</div>',
                                    ).apply(item);
                                }
                            },
                            {
                                text: '重置时间',
                                dataIndex: 'api_reset_at',
                                align: 'center',
                                width: 180,
                                renderer: function (value) {
                                    return value ? value : '-';
                                }
                            }
                        ]
                    },
                    {
                        text: '说明',
                        dataIndex: 'description',
                        align: 'center',
                        flex: 1,
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
                                                var url = '/api/configToken/' + record.id;
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
                    title: '令牌信息',
                    width: 500,
                    iconCls: 'icon-page-wrench',
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'form',
                            bodyPadding: 15,
                            items: [
                                {
                                    name: 'token',
                                    xtype: 'textfield',
                                    fieldLabel: '令牌',
                                    allowBlank: false,
                                    value: data.token,
                                },
                                {
                                    name: 'description',
                                    xtype: 'textfield',
                                    fieldLabel: '说明',
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
                                        var url = data.id ? '/api/configToken/' + data.id : '/api/configToken';
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
