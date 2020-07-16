@extends('base')
@section('content')
    <script>
        Ext.onReady(function () {
            Ext.QuickTips.init(true, {dismissDelay: 0});

            function btn(type) {
                return {
                    xtype: 'buttongroup',
                    baseCls: 'border:0',
                    items: [
                        {
                            text: '重置',
                            iconCls: 'icon-page-wrench',
                            margin: '0 15 0 105',
                            padding: '5 6 5 15',
                            width: 90,
                            handler: function () {
                                this.up('form').reset();
                            }
                        },
                        Ext.create('Ext.Button', {
                            text: '保存',
                            padding: '5 6 5 15',
                            iconCls: 'icon-page-edit',
                            width: 90,
                            handler: function () {
                                var values = this.up('form').getForm().getValues();
                                var enable = values.enable;
                                var interval = values.interval;
                                delete values.enable;
                                delete values.interval;
                                var params = {
                                    type: type,
                                    value: JSON.stringify(values),
                                    enable: enable,
                                    interval: interval,
                                }
                                tool.ajax('POST', '/api/configNotify', params, function (rsp) {
                                    if (rsp.success) {
                                        tool.toast('操作成功！', 'success');
                                    } else {
                                        tool.toast('操作失败！', 'error');
                                    }
                                });
                            }
                        })
                    ]
                }
            }

            var itemDefaults = {
                allowBlank: false,
                xtype: 'textfield',
                labelWidth: 100,
                width: 300,
                margin: '0 0 18 0',
            };

            var comboStore = Ext.create('Ext.data.Store', {
                data: [
                    {text: '是', value: 1},
                    {text: '否', value: 0},
                ]
            });

            var textAreaTip = function (field) {
                Ext.QuickTips.register({
                    target: field.el,
                    text: '支持多个，每行一个'
                });
            };

            var email = Ext.create('Ext.form.Panel', {
                title: '邮件',
                iconCls: 'icon-email',
                defaults: itemDefaults,
                items: [
                    {
                        xtype: 'combo',
                        name: 'host',
                        fieldLabel: '服 务 器',
                        store: Ext.create('Ext.data.Store', {
                            data: [
                                {value: 'smtp.qq.com'},
                                {value: 'smtp.163.com'},
                                {value: 'smtp.126.com'},
                                {value: 'smtp.aliyun.com'},
                                {value: 'smtp.gmail.com'},
                                {value: 'smtp.exmail.qq.com'}
                            ]
                        }),
                        queryMode: 'local',
                        displayField: 'value',
                        valueField: 'value',
                        typeAhead: true,
                        value: '{{$config['email']['value']['host']??''}}',
                    },
                    {
                        xtype: 'numberfield',
                        name: 'port',
                        fieldLabel: '端　　口',
                        value: '{{$config['email']['value']['port']??25}}',
                    },
                    {
                        name: 'username',
                        fieldLabel: '账　　号',
                        value: '{{$config['email']['value']['username']??''}}',
                    },
                    {
                        name: 'password',
                        fieldLabel: '授 权 码',
                        value: '{{$config['email']['value']['password']??''}}',
                    },
                    {
                        xtype: 'textareafield',
                        name: 'to',
                        fieldLabel: '接收邮箱',
                        value: "{{$config['email']['value']['to']??''}}",
                        emptyText: '支持多个，每行一个',
                    },
                    {
                        xtype: 'combo',
                        name: 'enable',
                        fieldLabel: '是否启用',
                        store: comboStore,
                        queryMode: 'local',
                        displayField: 'text',
                        valueField: 'value',
                        editable: false,
                        value: '{{$config['email']['enable']??0}}',
                    },
                    {
                        xtype: 'numberfield',
                        name: 'interval',
                        minValue: 1,
                        fieldLabel: '通知间隔',
                        value: '{{$config['email']['interval']??5}}',
                        listeners: {
                            render: function (c) {
                                Ext.QuickTips.register({
                                    target: c.getEl(),
                                    text: '分钟'
                                });
                            }
                        },
                    },
                    btn('email')
                ]
            });

            var dingTalk = Ext.create('Ext.form.Panel', {
                title: '钉钉',
                iconCls: 'icon-ding-talk',
                defaults: itemDefaults,
                tools: [{
                    iconCls: 'icon-cog',
                    tooltip: '钉钉文档',
                    handler: function () {
                        window.open('https://ding-doc.dingtalk.com/doc#/serverapi2/qf2nxq/e9d991e2')
                    },
                }],
                items: [
                    {
                        xtype: 'textfield',
                        name: 'webhook',
                        fieldLabel: 'webhook',
                        value: '{{$config['dingTalk']['value']['webhook']??''}}',
                    },
                    {
                        xtype: 'combo',
                        name: 'enable',
                        fieldLabel: '　是否启用',
                        store: comboStore,
                        queryMode: 'local',
                        displayField: 'text',
                        valueField: 'value',
                        editable: false,
                        value: '{{$config['dingTalk']['enable']??0}}',
                    },
                    {
                        xtype: 'numberfield',
                        name: 'interval',
                        minValue: 1,
                        fieldLabel: '　通知间隔',
                        value: '{{$config['dingTalk']['interval']??5}}',
                        listeners: {
                            render: function (c) {
                                Ext.QuickTips.register({
                                    target: c.getEl(),
                                    text: '分钟'
                                });
                            }
                        },
                    },
                    btn('dingTalk')
                ]
            });

            var workWechat = Ext.create('Ext.form.Panel', {
                title: '企业微信',
                iconCls: 'icon-work-wechat',
                defaults: itemDefaults,
                tools: [{
                    iconCls: 'icon-cog',
                    tooltip: '企业微信文档',
                    handler: function () {
                        window.open('https://work.weixin.qq.com/help?person_id=1&doc_id=13376')
                    },
                }],
                items: [
                    {
                        xtype: 'textfield',
                        name: 'webhook',
                        fieldLabel: 'webhook',
                        value: '{{$config['workWechat']['value']['webhook']??''}}',
                    },
                    {
                        xtype: 'combo',
                        name: 'enable',
                        fieldLabel: '　是否启用',
                        store: comboStore,
                        queryMode: 'local',
                        displayField: 'text',
                        valueField: 'value',
                        editable: false,
                        value: '{{$config['workWechat']['enable']??0}}',
                    },
                    {
                        xtype: 'numberfield',
                        name: 'interval',
                        minValue: 1,
                        fieldLabel: '　通知间隔',
                        value: '{{$config['workWechat']['interval']??5}}',
                        listeners: {
                            render: function (c) {
                                Ext.QuickTips.register({
                                    target: c.getEl(),
                                    text: '分钟'
                                });
                            }
                        },
                    },
                    btn('workWechat')
                ]
            });

            var telegram = Ext.create('Ext.form.Panel', {
                title: 'Telegram',
                iconCls: 'icon-telegram',
                defaults: itemDefaults,
                tools: [{
                    iconCls: 'icon-cog',
                    tooltip: 'Telegram文档',
                    handler: function () {
                        window.open('https://core.telegram.org/bots/api')
                    },
                }],
                items: [
                    {
                        xtype: 'textfield',
                        name: 'token',
                        fieldLabel: '　token',
                        value: '{{$config['telegram']['value']['token']??''}}',
                    },
                    {
                        xtype: 'textfield',
                        name: 'chat_id',
                        fieldLabel: 'chat_id',
                        value: '{{$config['telegram']['value']['chat_id']??''}}',
                    },
                    {
                        xtype: 'combo',
                        name: 'enable',
                        fieldLabel: '是否启用',
                        store: comboStore,
                        queryMode: 'local',
                        displayField: 'text',
                        valueField: 'value',
                        editable: false,
                        value: '{{$config['telegram']['enable']??0}}',
                    },
                    {
                        xtype: 'numberfield',
                        name: 'interval',
                        minValue: 1,
                        fieldLabel: '通知间隔',
                        value: '{{$config['telegram']['interval']??5}}',
                        listeners: {
                            render: function (c) {
                                Ext.QuickTips.register({
                                    target: c.getEl(),
                                    text: '分钟'
                                });
                            }
                        },
                    },
                    btn('telegram')
                ]
            });

            var webhook = Ext.create('Ext.form.Panel', {
                title: 'Webhook',
                iconCls: 'icon-page-wrench',
                defaults: itemDefaults,
                items: [
                    {
                        xtype: 'textfield',
                        name: 'webhook',
                        fieldLabel: 'webhook',
                        value: '{{$config['webhook']['value']['webhook']??''}}',
                    },
                    {
                        xtype: 'textareafield',
                        name: 'headers',
                        fieldLabel: '请求头',
                        value: "{{$config['webhook']['value']['headers']??''}}",
                        emptyText: '每行一个,格式如：\nkey: value',
                    },
                    {
                        xtype: 'combo',
                        name: 'enable',
                        fieldLabel: '是否启用',
                        store: comboStore,
                        queryMode: 'local',
                        displayField: 'text',
                        valueField: 'value',
                        editable: false,
                        value: '{{$config['webhook']['enable']??0}}',
                    },
                    {
                        xtype: 'numberfield',
                        name: 'interval',
                        minValue: 1,
                        fieldLabel: '通知间隔',
                        value: '{{$config['webhook']['interval']??5}}',
                        listeners: {
                            render: function (c) {
                                Ext.QuickTips.register({
                                    target: c.getEl(),
                                    text: '分钟'
                                });
                            }
                        },
                    },
                    btn('webhook')
                ]
            });


            Ext.create('Ext.panel.Panel', {
                renderTo: Ext.getBody(),
                layout: 'column',
                margin: '0 10 0 10',
                defaults: {
                    layout: 'form',
                    columnWidth: 1 / 3,
                    margin: 5,
                    height: 440,
                    iconCls: 'icon-page',
                    bodyPadding: 30,
                    bodyStyle: 'background:#FAFAFA'
                },
                items: [email, dingTalk, workWechat, telegram, webhook]
            });
        })
        ;
    </script>
@endsection
