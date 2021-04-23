@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/home.css?v=') . VERSION }}">
    <script type="text/javascript" src="{{ URL::asset('js/g2.min.js') }}"></script>

    <script>
        Ext.onReady(function () {
            var tplMetric = new Ext.XTemplate(
                '<div class="metric">',
                '    <p class="metric-value" data-key="{key}">0</p>',
                '    <p class="content">{name}</p>',
                '</div>',
            );

            Ext.create('Ext.container.Container', {
                id: 'container',
                renderTo: Ext.getBody(),
                layout: 'vbox',
                padding: 25,
                viewModel: {
                    data: {
                        clock: '',
                        load: ['未知', '未知', '未知'],
                        configToken: 'cross',
                        configJob: 'cross',
                        versionTip: '当前已是最新版',
                        disk: {
                            used: '未知',
                            total: '未知',
                            percent: 0,
                        },
                        memory: {
                            used: '未知',
                            total: '未知',
                            percent: 0,
                        }
                    }
                },
                items: [
                    {
                        xtype: 'container',
                        layout: 'column',
                        width: '100%',
                        defaults: {
                            columnWidth: 0.25,
                            height: 100,
                        },
                        items: [
                            {
                                bodyPadding: '13 25',
                                bind: {
                                    html: '<p class="title">{{ $user }}</p><p class="content">{clock}</p>',
                                }
                            },
                            {
                                layout: 'hbox',
                                columnWidth: 0.5,
                                margin: '0 15',
                                bodyStyle: 'background:#FFF',
                                defaults: {
                                    margin: '0 15 0 0',
                                    width: '25%',
                                },
                                items: [
                                    {
                                        html: tplMetric.apply({
                                            key: 'codeLeakCount',
                                            name: '扫描记录数',
                                        })
                                    },
                                    {
                                        html: tplMetric.apply({
                                            key: 'codeLeakPending',
                                            name: '未审记录数',
                                        })
                                    },
                                    {
                                        html: tplMetric.apply({
                                            key: 'codeLeakSolved',
                                            name: '已解决泄露',
                                        })
                                    },
                                    {
                                        margin: 0,
                                        html: tplMetric.apply({
                                            key: 'queueJobCount',
                                            name: '待执行任务',
                                        })
                                    }
                                ]
                            },
                            {
                                bodyPadding: '14 0',
                                bind: {
                                    html: new Ext.XTemplate(
                                        '<div class="center">',
                                        '    <p class="title">配置状态</p>',
                                        '    <p class="content">',
                                        '        <span class="config-item">',
                                        '            令牌配置：<span class="config-icon x-btn icon-{token}"></span>',
                                        '        </span>',
                                        '        <span class="config-item">',
                                        '            任务配置：<span class="config-icon x-btn icon-{job}"></span>',
                                        '        </span>',
                                        '    </p>',
                                        '</div>',
                                    ).apply({
                                        token: '{configToken}',
                                        job: '{configJob}',
                                    })
                                }
                            }
                        ],
                    },
                    {
                        xtype: 'container',
                        layout: 'column',
                        margin: '15 0 0 0',
                        width: '100%',
                        defaults: {
                            columnWidth: 0.25,
                            height: 315,
                        },
                        items: [
                            {
                                layout: 'vbox',
                                bodyStyle: 'background:#FFF',
                                defaults: {
                                    width: '100%',
                                    bodyPadding: '10 25',
                                },
                                items: [
                                    {
                                        height: 200,
                                        bind: {
                                            html: new Ext.XTemplate(
                                                '<p class="title">主机监控</p>',
                                                '<p class="content">系统负载：{load1} / {load5} / {load15}</p>',
                                                '<p class="content">内存信息：{memoryUsed} / {memoryTotal}</p>',
                                                '<p>',
                                                '    <div class="progress">',
                                                '        <div style="width:{memoryPercent}%"></div>',
                                                '    </div>',
                                                '</p>',
                                                '<p class="content">磁盘空间：{diskUsed} / {diskTotal}</p>',
                                                '<p>',
                                                '    <div class="progress">',
                                                '        <div style="width:{diskPercent}%"></div>',
                                                '    </div>',
                                                '</p>',
                                            ).apply({
                                                load1: '{load.0}',
                                                load5: '{load.1}',
                                                load15: '{load.2}',
                                                memoryUsed: '{memory.used}',
                                                memoryTotal: '{memory.total}',
                                                memoryPercent: '{memory.percent}',
                                                diskUsed: '{disk.used}',
                                                diskTotal: '{disk.total}',
                                                diskPercent: '{disk.percent}',
                                            })
                                        }
                                    },
                                    {
                                        margin: '15 0 0 0',
                                        height: 100,
                                        bind: {
                                            html: new Ext.XTemplate(
                                                '    <p class="title">版本信息</p>',
                                                '    <p class="content">{version}（{versionTip}）</p>',
                                            ).apply({
                                                version: '{{ VERSION }}',
                                                versionTip: '{versionTip}',
                                            })
                                        }
                                    }
                                ]
                            },
                            {
                                columnWidth: 0.5,
                                margin: '0 15',
                                bodyPadding: '70 0 0 0',
                                height: 315,
                                html: new Ext.XTemplate(
                                    '<p class="center"><a target="_blank" href="{repo}"><img src="{src}" /></a></p>',
                                ).apply({
                                    repo: 'https://github.com/4x99/code6',
                                    src: '{{ URL::asset("image/logo-home.png") }}',
                                })
                            },
                            {
                                bodyPadding: '60 0',
                                html: '<div id="tokenQuota" class="center"></div>',
                            }
                        ]
                    }
                ]
            });

            var viewModel = Ext.getCmp('container').getViewModel();
            var taskRunner = new Ext.util.TaskRunner();

            function newTask(interval, run) {
                var task = taskRunner.newTask({
                    run: run,
                    interval: interval,
                    fireOnStart: true,
                });
                task.start();
                return task;
            }

            // 动态时间
            newTask(1000, function () {
                viewModel.setData({clock: Ext.Date.format(new Date(), 'Y-m-d H:i:s')});
            });

            // 数据指标
            newTask(60000, function () {
                tool.ajax('GET', '/api/home/metric', {}, function (rsp) {
                    if (rsp.success !== true) {
                        return false;
                    }

                    Ext.each(Ext.query('[class=metric-value]'), function (dom) {
                        var value = 0;
                        var total = rsp.data[dom.dataset.key];
                        var step = Math.ceil(total / 20);
                        var task = newTask(30, function () {
                            if ((value += step) >= total) {
                                task.stop();
                                value = total;
                            }
                            dom.innerHTML = value;
                        })
                    });
                });
            });

            // 负载 + 内存 + 磁盘信息
            Ext.each(['load', 'memory', 'disk'], function (key) {
                newTask(60000, function () {
                    tool.ajax('GET', '/api/home/' + key, {}, function (rsp) {
                        if (rsp.success) {
                            var data = {};
                            data[key] = rsp.data;
                            viewModel.setData(data);
                        }
                    });
                });
            })

            // GitHub 接口请求环形图
            const chart = new G2.Chart({
                container: 'tokenQuota',
                height: 200,
                autoFit: true,
            });

            chart.legend(false);

            chart.coordinate('theta', {
                radius: 1,
                innerRadius: 0.9,
            });

            chart.tooltip({
                showTitle: false,
                showMarkers: false,
            });

            chart.annotation().text({
                position: ['50%', '50%'],
                offsetY: -15,
                content: 'G i t H u b',
                style: {
                    fill: '#999',
                    textAlign: 'center',
                }
            });

            chart.annotation().text({
                position: ['50%', '50%'],
                offsetY: 15,
                content: '接 口 请 求 统 计',
                style: {
                    fill: '#999',
                    textAlign: 'center',
                }
            });

            var chartConfig = chart.interval().adjust('stack').position('percent').color('name', ['#1890FF', '#F0F0F0']);
            chartConfig.tooltip('name*value', (name, value) => {
                return {
                    name: name,
                    value: value,
                };
            });

            chart.data([
                {name: '可用', value: 0, percent: 0},
                {name: '已用', value: 0, percent: 0},
            ]);

            chart.render();

            // GitHub 接口请求统计
            newTask(60000, function () {
                tool.ajax('GET', '/api/home/tokenQuota', {}, function (rsp) {
                    if (rsp.success) {
                        chart.changeData(rsp.data);
                    }
                });
            });

            // 检查令牌
            tool.ajax('GET', '/api/home/tokenCount', {}, function (rsp) {
                if (rsp.data) {
                    viewModel.setData({configToken: 'tick'});
                } else {
                    showHelp();
                }
            });

            // 检查任务
            tool.ajax('GET', '/api/home/jobCount', {}, function (rsp) {
                if (rsp.data) {
                    viewModel.setData({configJob: 'tick'});
                }
            });

            // 检查最新版本
            tool.ajax('GET', '/api/home/upgradeCheck', {}, function (rsp) {
                if (rsp.data && rsp.data.new) {
                    var version = rsp.data.version;
                    var url = 'https://github.com/4x99/code6/releases';
                    var text = '发现新版本：<a class="version" href="' + url + '" target="_blank">' + version + '</a>';
                    viewModel.setData({versionTip: text});
                }
            });

            function showHelp() {
                var msg = [];
                msg.push('<b>扫 描 流 程</b>\n');
                msg.push('＋－－－－－－－－－－－－－＋　　　　令　牌　　　　＋－－－－－－－－－－－－－－＋');
                msg.push('｜　　　　　　　　　　　　　｜－－－－－－－－－－＞｜　　　　　　　　　　　　　　｜');
                msg.push('｜　　　码　　小　　六　　　｜　　　　　　　　　　　｜　　ＧｉｔＨｕｂ　ＡＰＩ　　｜');
                msg.push('｜　　　　　　　　　　　　　｜＜－－－－－－－－－－｜　　　　　　　　　　　　　　｜');
                msg.push('＋－－－－－－－－－－－－－＋　　扫　描　结　果　　＋－－－－－－－－－－－－－－＋');
                msg.push('\n');
                msg.push('<b>配 置 说 明</b>\n');
                msg.push('<p>[ 配置中心 ] - [ 令牌配置 ]：设置从官网申请到的接口令牌</p>');
                msg.push('<p>[ 配置中心 ] - [ 任务配置 ]：设置需要扫描的关键字及参数</p>');

                Ext.Msg.show({
                    title: '新手引导',
                    modal: false,
                    iconCls: 'icon-page-star',
                    width: 700,
                    maxWidth: 700,
                    message: '<pre class="help">' + msg.join('\n') + '</pre>',
                }).removeCls('x-unselectable');
            }
        });
    </script>
@endsection
