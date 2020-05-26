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
                                margin: '0 100 0 20',
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
                                            },
                                            {
                                                text: '任务配置',
                                                url: '/configJob',
                                            },
                                            {
                                                text: '令牌配置',
                                                url: '/configToken',
                                            },
                                            {
                                                text: '白名单配置',
                                                url: '/configWhitelist',
                                            },
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
                                text: '个人中心',
                                iconCls: 'icon-page-wrench',
                                margin: '0 25 0 0',
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

            // 关闭动画
            Ext.getDom('frame').onload = function () {
                Ext.get('loading').setStyle('display', 'none');
            };
        })
    </script>
@endsection
