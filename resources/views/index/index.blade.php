@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/index.css?v=') . VERSION }}">

    <div id="loading">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>

    <script>
        Ext.onReady(function () {
            Ext.QuickTips.init();

            Ext.create('Ext.container.Container', {
                renderTo: Ext.getBody(),
                height: '100%',
                layout: 'border',
                items: [
                    {
                        region: 'north',
                        height: 64,
                        xtype: 'toolbar',
                        border: '0 0 1 0',
                        style: 'background:#FAFAFA',
                        items: [
                            {
                                xtype: 'image',
                                src: '{{ URL::asset("image/logo.png") }}',
                                width: 120,
                                height: 26,
                                margin: '0 20 0 20',
                            },
                            '->',
                            {
                                id: 'nav',
                                xtype: 'component',
                                viewModel: {
                                    data: {
                                        nav: [
                                            {
                                                text: '应用概况',
                                                url: '/home',
                                                active: true,
                                            },
                                            {
                                                text: '扫描结果',
                                                url: '/codeLeak',
                                            }
                                        ]
                                    }
                                },
                                bind: {
                                    data: '{nav}',
                                },
                                tpl: [
                                    '<ul class="nav">',
                                    '    <tpl for=".">',
                                    '    <li><a href="{url}" onclick="Ext.clickMenu({#})"',
                                    '     target="frame"<tpl if="active"> class="active"</tpl>>{text}</a></li>',
                                    '    </tpl>',
                                    '</ul>',
                                ]
                            },
                            {
                                text: '配置中心',
                                iconCls: 'icon-cog',
                                margin: '0 40 0 -15',
                                menu: {
                                    xtype: 'menu',
                                    items: {
                                        xtype: 'buttongroup',
                                        columns: 2,
                                        defaults: {
                                            width: 120,
                                            margin: '0 5 5 0',
                                            hrefTarget: 'frame',
                                            handler: function () {
                                                Ext.clickMenu(-1);
                                            }
                                        },
                                        items: [
                                            {
                                                text: '令牌配置',
                                                iconCls: 'icon-page-key',
                                                href: '/configToken',
                                            },
                                            {
                                                text: '任务配置',
                                                iconCls: 'icon-page-star',
                                                href: '/configJob',
                                            },
                                            {
                                                text: '通知配置',
                                                iconCls: 'icon-email',
                                                href: '/configNotify',
                                            },
                                            {
                                                text: '白名单配置',
                                                align: 'left',
                                                iconCls: 'icon-page-db',
                                                href: '/configWhitelist',
                                            },
                                            {
                                                text: '代理配置',
                                                align: 'left',
                                                iconCls: 'icon-page-wrench',
                                                handler: function () {
                                                    winForm([]);
                                                }
                                            }
                                        ]
                                    }
                                }
                            },
                            {
                                text: '个人中心',
                                iconCls: 'icon-user',
                                menu: {
                                    xtype: 'menu',
                                    items: {
                                        xtype: 'buttongroup',
                                        columns: 2,
                                        items: [
                                            {
                                                text: '修改密码',
                                                margin: '0 5 0 0',
                                                iconCls: 'icon-key',
                                                handler: function () {
                                                    Ext.resetPassword();
                                                }
                                            },
                                            {
                                                text: '退出登录',
                                                iconCls: 'icon-go',
                                                handler: function () {
                                                    Ext.Msg.show({
                                                        title: '提示',
                                                        iconCls: 'icon-page',
                                                        message: '确认退出系统？',
                                                        buttons: Ext.Msg.YESNO,
                                                        modal: false,
                                                        fn: function (btn) {
                                                            if (btn !== 'yes') {
                                                                return
                                                            }

                                                            tool.ajax('POST', '/api/logout', {}, function (rsp) {
                                                                if (rsp.success) {
                                                                    window.location = '/login';
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
                            },
                            {
                                id: 'mobile',
                                iconCls: 'icon-phone',
                                href: '/mobile',
                                text: '访问移动版',
                                margin: '0 25 0 32',
                            }
                        ]
                    },
                    {
                        region: 'center',
                        border: false,
                        bodyPadding: '10 0 0 0',
                        html: '<iframe id="frame" name="frame" src="/home" width="100%" height="100%"></iframe>',
                    }
                ]
            });

            // 点击菜单
            Ext.clickMenu = function (index) {
                var nav = [];
                var navViewModel = Ext.getCmp('nav').getViewModel();
                Ext.each(navViewModel.get('nav'), function (item, i) {
                    item.active = (index === i + 1);
                    nav.push(item);
                });
                navViewModel.set('nav', nav);
                Ext.get('loading').setStyle('display', 'block');
            }

            // 修改密码
            Ext.resetPassword = function () {
                var win = Ext.create('Ext.window.Window', {
                    title: '修改密码',
                    iconCls: 'icon-key',
                    width: 350,
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'form',
                            layout: 'form',
                            bodyPadding: 15,
                            defaults: {
                                xtype: 'textfield',
                                inputType: 'password',
                                labelAlign: 'right',
                                allowBlank: false,
                            },
                            items: [
                                {
                                    name: 'password_current',
                                    fieldLabel: '当前密码',
                                },
                                {
                                    name: 'password_new',
                                    fieldLabel: '输入新密码',
                                    minLength: 6,
                                    maxLength: 16,
                                },
                                {
                                    name: 'password_new_confirmation',
                                    fieldLabel: '再次输入新密码',
                                    minLength: 6,
                                    maxLength: 16,
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
                                        var params = this.up('form').getForm().getValues();
                                        if (params.password_new !== params.password_new_confirmation) {
                                            tool.toast('两次输入的密码不一致！');
                                            return false;
                                        }
                                        tool.ajax('PUT', '/api/user', params, function (rsp) {
                                            if (rsp.success) {
                                                win.close();
                                                tool.toast('操作成功！', 'success');
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

            // 移动版二维码
            Ext.create('Ext.tip.ToolTip', {
                target: 'mobile',
                width: 300,
                height: 300,
                loader: {
                    url: '/api/home/mobileQrCode',
                    loadOnRender: true,
                },
            });

            // 关闭动画
            Ext.getDom('frame').onload = function () {
                Ext.get('loading').setStyle('display', 'none');
            };

            function winForm() {
                tool.ajax('GET', '/api/configProxy', {}, function (rsp) {
                    if (!rsp.success) {
                        tool.toast('读取配置错误！');
                        return false;
                    }

                    var winProxy = Ext.create('Ext.window.Window', {
                        title: '代理配置',
                        iconCls: 'icon-folder-page',
                        width: 300,
                        layout: 'fit',
                        items: [
                            {
                                xtype: 'form',
                                layout: 'form',
                                bodyPadding: '5 15 15 8',
                                items: [
                                    {
                                        xtype: 'textfield',
                                        name: 'value',
                                        value: rsp.data,
                                    }
                                ],
                                buttons: [
                                    {
                                        text: '代理测试',
                                        handler: function () {
                                            var params = this.up('form').getValues();
                                            if (!params['value']) {
                                                tool.toast('代理不能为空！', 'error');
                                                return;
                                            }
                                            tool.ajax('POST', '/api/configProxy/test', params, function (rsp) {
                                                if (rsp.success) {
                                                    tool.toast('测试成功！', 'success');
                                                } else {
                                                    tool.toast(rsp.message, 'error');
                                                }
                                            });
                                        }
                                    },
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
                                            tool.ajax('POST', '/api/configProxy', params, function (rsp) {
                                                if (rsp.success) {
                                                    winProxy.close();
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
        })
    </script>
@endsection
