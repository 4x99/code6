@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            Ext.QuickTips.init(true, {dismissDelay: 0});

            Ext.define('panel', {
                extend: 'Ext.form.Panel',
                defaults: {
                    xtype: 'textfield',
                    width: '100%',
                    margin: '0 0 15 0',
                    allowBlank: false,
                    labelAlign: 'right',
                    labelWidth: 85,
                }
            });

            var email = Ext.create('panel', {
                title: '邮件',
                iconCls: 'icon-email',
                items: [
                    createEnableField('email'),
                    {
                        xtype: 'combo',
                        name: 'host',
                        fieldLabel: '服 务 器',
                        store: {
                            data: [
                                {text: 'smtp.qq.com'},
                                {text: 'smtp.163.com'},
                                {text: 'smtp.126.com'},
                                {text: 'smtp.aliyun.com'},
                                {text: 'smtp.gmail.com'},
                                {text: 'smtp.exmail.qq.com'},
                            ]
                        },
                        queryMode: 'local',
                        valueField: 'text',
                        typeAhead: true,
                        value: getConfig('email.value.host'),
                    },
                    {
                        xtype: 'numberfield',
                        name: 'port',
                        fieldLabel: '端　　口',
                        value: getConfig('email.value.port', 465),
                        allowBlank: true,
                        listeners: {
                            render: function (c) {
                                Ext.QuickTips.register({
                                    target: c.getEl(),
                                    text: '已启用 SSL 协议，通常使用 465 而非 25 端口',
                                });
                            }
                        }
                    },
                    {
                        name: 'username',
                        fieldLabel: '账　　号',
                        value: getConfig('email.value.username'),
                    },
                    {
                        name: 'password',
                        fieldLabel: '密　　码',
                        emptyText: '请填写授权码 ..',
                        value: getConfig('email.value.password'),
                    },
                    {
                        xtype: 'textareafield',
                        name: 'to',
                        fieldLabel: '接收邮箱',
                        emptyText: '支持多个邮箱（一行一个）',
                        value: getConfig('email.value.to'),
                    },
                    createIntervalField('email'),
                    createTimeField('email'),
                    createBtn('email'),
                ]
            });

            var webhook = Ext.create('panel', {
                title: 'Webhook',
                iconCls: 'icon-page-star',
                tools: [{
                    iconCls: 'icon-page',
                    tooltip: 'Webhook 文档',
                    handler: function () {
                        Ext.Msg.show({
                            title: 'Webhook文档',
                            iconCls: 'icon-page',
                            modal: false,
                            message: '请求方式：post<br/>请求参数：content',
                        });
                    }
                }],
                items: [
                    createEnableField('webhook'),
                    createWebhookField('webhook'),
                    {
                        xtype: 'textareafield',
                        name: 'headers',
                        fieldLabel: '请求头部',
                        allowBlank: true,
                        emptyText: '示例：\nUser-Agent: Code6\nContent-Type: application/json;charset=utf8',
                        value: getConfig('webhook.value.headers'),
                    },
                    createIntervalField('webhook'),
                    createTimeField('webhook'),
                    createBtn('webhook'),
                ]
            });

            var telegram = Ext.create('panel', {
                title: 'Telegram',
                iconCls: 'icon-telegram',
                tools: [{
                    iconCls: 'icon-page',
                    tooltip: 'Telegram 文档',
                    handler: function () {
                        tool.winOpen('https://core.telegram.org/bots/api');
                    },
                }],
                items: [
                    createEnableField('telegram'),
                    {
                        name: 'chat_id',
                        fieldLabel: '频　　道',
                        value: getConfig('telegram.value.chat_id'),
                        emptyText: '请填写 chat_id ..',
                    },
                    {
                        name: 'token',
                        fieldLabel: '令　　牌',
                        value: getConfig('telegram.value.token'),
                        emptyText: '请填写 token ..',
                    },
                    createIntervalField('telegram'),
                    createTimeField('telegram'),
                    createBtn('telegram'),
                ]
            });

            var dingTalk = Ext.create('panel', {
                title: '钉钉',
                iconCls: 'icon-ding-talk',
                height: 280,
                tools: [{
                    iconCls: 'icon-page',
                    tooltip: '钉钉文档',
                    handler: function () {
                        tool.winOpen('https://ding-doc.dingtalk.com/doc#/serverapi2/qf2nxq');
                    }
                }],
                items: [
                    createEnableField('dingTalk'),
                    createWebhookField('dingTalk'),
                    createIntervalField('dingTalk'),
                    createTimeField('dingTalk'),
                    createBtn('dingTalk'),
                ]
            });

            var workWechat = Ext.create('panel', {
                title: '企业微信',
                iconCls: 'icon-work-wechat',
                height: 280,
                tools: [{
                    iconCls: 'icon-page',
                    tooltip: '企业微信文档',
                    handler: function () {
                        tool.winOpen('https://work.weixin.qq.com/help?doc_id=13376');
                    }
                }],
                items: [
                    createEnableField('workWechat'),
                    createWebhookField('workWechat'),
                    createIntervalField('workWechat'),
                    createTimeField('workWechat'),
                    createBtn('workWechat'),
                ]
            });

            function createBtn(type) {
                return {
                    xtype: 'buttongroup',
                    baseCls: 'border:0',
                    layout: {
                        type: 'hbox',
                        pack: 'end',
                    },
                    defaults: {
                        padding: '4 8 4 13',
                        width: 100,
                    },
                    items: [
                        {
                            text: '重置',
                            iconCls: 'icon-page-wrench',
                            handler: function () {
                                this.up('form').reset();
                            }
                        },
                        {
                            text: '测试',
                            iconCls: 'icon-page-lightning',
                            margin: '0 20',
                            tooltip: '测试发送通知',
                            handler: function () {
                                var form = this.up('form');
                                if (!form.isValid()) {
                                    tool.toast('信息有误！');
                                    return false;
                                }

                                var params = this.up('form').getForm().getValues();
                                params.type = type;
                                tool.ajax('POST', '/api/test', params, function (rsp) {
                                    if (rsp.success) {
                                        tool.toast('发送成功！', 'success');
                                    } else {
                                        tool.toast(rsp.message, 'error');
                                    }
                                });
                            }
                        },
                        {
                            text: '保存',
                            iconCls: 'icon-page-edit',
                            handler: function () {
                                var form = this.up('form');
                                if (!form.isValid()) {
                                    tool.toast('信息有误！');
                                    return false;
                                }

                                var params = this.up('form').getForm().getValues();
                                params.type = type;
                                tool.ajax('POST', '/api/configNotify', params, function (rsp) {
                                    if (rsp.success) {
                                        tool.toast('保存成功！', 'success');
                                    } else {
                                        tool.toast('保存失败！', 'error');
                                    }
                                });
                            }
                        }
                    ]
                }
            }

            function createEnableField(type) {
                return {
                    xtype: 'combo',
                    name: 'enable',
                    fieldLabel: '是否启用',
                    store: {
                        data: [
                            {text: '是', value: 1},
                            {text: '否', value: 0},
                        ]
                    },
                    valueField: 'value',
                    editable: false,
                    value: getConfig(type + '.enable', 0),
                };
            }

            function createIntervalField(type) {
                return {
                    xtype: 'numberfield',
                    name: 'interval_min',
                    minValue: 1,
                    fieldLabel: '通知间隔',
                    step: 5,
                    value: getConfig(type + '.interval_min', 5),
                    listeners: {
                        render: function (c) {
                            Ext.QuickTips.register({
                                target: c.getEl(),
                                text: '分钟',
                            });
                        }
                    }
                };
            }

            function createTimeField(type) {
                return {
                    xtype: 'container',
                    layout: 'hbox',
                    width: '100%',
                    defaults: {
                        xtype: 'timefield',
                        format: 'H:i:s',
                        increment: 1,
                        allowBlank: false,
                    },
                    items: [
                        {
                            xtype: 'label',
                            margin: '4 5',
                            text: '通知时段：',
                            style: 'letter-spacing:3px'
                        },
                        {
                            name: 'start_time',
                            flex: 1,
                            value: getConfig(type + '.start_time', '08:00'),
                        },
                        {
                            xtype: 'label',
                            margin: '4 10',
                            text: '-',
                        },
                        {
                            name: 'end_time',
                            flex: 1,
                            value: getConfig(type + '.end_time', '22:00'),
                        }
                    ]
                };
            }

            function createWebhookField(type) {
                return {
                    name: 'webhook',
                    fieldLabel: '地　　址',
                    value: getConfig(type + '.value.webhook'),
                    emptyText: '请填写 webhook 地址 ..',
                };
            }

            // 读取配置
            function getConfig(key = '', defaultValue = '') {
                var data = @json($config);
                Ext.each(key.split('.'), function (attr) {
                    data = data[attr];
                    data = data ? data : defaultValue;
                    return data !== undefined;
                });
                return data;
            }

            Ext.create('Ext.panel.Panel', {
                renderTo: Ext.getBody(),
                layout: 'column',
                margin: '5 15 0 15',
                defaults: {
                    layout: 'form',
                    columnWidth: 1 / 3,
                    margin: 10,
                    height: 450,
                    bodyPadding: 20,
                    bodyStyle: 'background:#FAFAFA',
                },
                items: [email, webhook, telegram, dingTalk, workWechat],
            });
        });
    </script>
@endsection
