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
                                html: new Ext.XTemplate(
                                    '<div class="center">',
                                    '    <p class="title">版本信息</p>',
                                    '    <p class="content">{version}（GPL v3）</p>',
                                    '</div>',
                                ).apply({
                                    version: '{{ VERSION }}',
                                })
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
                                        margin: '25 0 0 0',
                                        height: 100,
                                        html: new Ext.XTemplate(
                                            '    <p class="title">运行环境</p>',
                                            '    <p class="content">PHP {phpVersion} + Laravel {laravelVersion}</p>',
                                        ).apply({
                                            phpVersion: '{{ PHP_VERSION }}',
                                            laravelVersion: '{{ app()::VERSION }}',
                                        })
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

            // 检查任务
            tool.ajax('GET', '/api/home/jobCount', {}, function (rsp) {
                if (!rsp.data) {
                    tool.toast('尚未配置扫描任务<br/>请前往 [ 任务配置 ] 模块配置！', 'warning', 15000);
                }
            });

            // 检查令牌
            tool.ajax('GET', '/api/home/tokenCount', {}, function (rsp) {
                if (!rsp.data) {
                    tool.toast('尚未配置 GitHub 令牌<br/>请前往 [ 令牌配置 ] 模块配置！', 'warning', 15000);
                }
            });
        });
    </script>
@endsection
