<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="version" content="{{ VERSION }}">
    <link rel="icon" href="data:;base64,=">
    <link rel="stylesheet" href="{{ URL::asset('css/vant.css')}}">
    <link rel="stylesheet" href="{{ URL::asset('css/mobile.css')}}">
    <script type="text/javascript" src="{{ URL::asset('js/vue.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/axios.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/vant.min.js') }}"></script>
</head>
<body>
<div id="app">
    <div class="header">
        <div class="logo"></div>
    </div>
    <van-pull-refresh v-model="loading" success-text="刷新成功" @refresh="refresh">
        <div class="overview">
            <div class="metric metric-left">
                <p class="metric-value">[[codeLeakCount]]</p>
                <p class="metric-name">扫描记录数</p>
            </div>
            <div class="metric metric-right">
                <p class="metric-value">[[codeLeakPending]]</p>
                <p class="metric-name">未审记录数</p>
            </div>
        </div>
        <div class="codeLeak">
            <van-dropdown-menu>
                <van-dropdown-item v-model="status" :options="statusOptions" @change="loadCodeLeak(1)"/>
            </van-dropdown-menu>
            <van-list>
                <template v-if="codeLeaks.length > 0">
                    <van-cell v-for="codeLeak in codeLeaks" @click="showAction(codeLeak)">
                        <div>
                            <span v-html="formatStatus(codeLeak.status)"></span>
                            <span>[[codeLeak.created_at]]</span>
                        </div>
                        <div>
                            仓　库：[[codeLeak.repo_owner]]/[[codeLeak.repo_name]]
                        </div>
                        <div>
                            关键字：[[codeLeak.keyword]]
                        </div>
                    </van-cell>
                    <van-pagination v-model="currentPage" :page-count="pageCount" @change="loadCodeLeak(currentPage)" mode="simple"/>
                </template>
                <template v-else-if="finished">
                    <van-empty description="暂 无 数 据"/>
                </template>
            </van-list>
            <van-action-sheet v-model="show" :actions="actions" @select="onSelect"></van-action-sheet>
        </div>
    </van-pull-refresh>
</div>
</body>
<script>
    let STATUS_PENDING = 0;
    let STATUS_FALSE = 1;
    let STATUS_ABNORMAL = 2;
    let STATUS_SOLVED = 3;
    let GitHub = 'https://github.com/';

    new Vue({
        el: '#app',
        delimiters: ['[[', ']]'],
        data: {
            loading: false,
            finished: false,
            show: false,
            status: '',
            statusOptions: [
                {"value": '', "text": "全部状态"},
                {"value": 0, "text": "未审"},
                {"value": 1, "text": "误报"},
                {"value": 2, "text": "异常"},
                {"value": 3, "text": "解决"}
            ],
            actions: [
                {name: '设为未审', color: '#738C99'},
                {name: '设为误报', color: '#409EFF'},
                {name: '设为异常', color: '#F56C6C'},
                {name: '设为解决', color: '#67C23A'},
                {name: '查看源码'},
            ],
            codeLeakCount: 0,
            codeLeakPending: 0,
            codeLeaks: [],
            currentPage: 1,
            pageCount: 0,
        },
        methods: {
            load() {
                this.loadMetric();
                this.loadCodeLeak(1);
            },
            refresh() {
                this.load();
                this.loading = false;
                this.finished = true;
            },
            loadMetric() {
                let me = this;
                axios.get('/api/home/metric').then(function (rsp) {
                    me.codeLeakCount = rsp.data.data.codeLeakCount;
                    me.codeLeakPending = rsp.data.data.codeLeakPending;
                }).catch(function (rsp) {
                });
            },
            loadCodeLeak(page) {
                page = page ? page : 1;
                let me = this;
                let url = '/api/codeLeak?limit=20&status=' + me.status + '&page=' + page
                axios.get(url).then(function (rsp) {
                    me.codeLeaks = rsp.data.data ? rsp.data.data : [];
                    me.pageCount = rsp.data.last_page ? rsp.data.last_page : 0;
                }).catch(function (rsp) {
                    me.$toast.fail(rsp);
                });
            },
            showAction(codeLeak) {
                this.codeLeak = codeLeak;
                this.show = true;
            },
            formatStatus(status) {
                let conf = {
                    0: {color: '#738C99', text: '未审'},
                    1: {color: '#409EFF', text: '误报'},
                    2: {color: '#F56C6C', text: '异常'},
                    3: {color: '#67C23A', text: '解决'},
                }
                return '<div class="tag" style="background:' + conf[status].color + '">' + conf[status].text + '</div>';
            },
            onSelect(item) {
                let me = this;
                this.show = false;
                switch (item.name) {
                    case '设为未审':
                        me.updateStatus(STATUS_PENDING);
                        break;
                    case '设为误报':
                        me.updateStatus(STATUS_FALSE);
                        break;
                    case '设为异常':
                        me.updateStatus(STATUS_ABNORMAL);
                        break;
                    case '设为解决':
                        me.updateStatus(STATUS_SOLVED);
                        break;
                    case '查看源码':
                        let url = GitHub + me.codeLeak.repo_owner + '/' + me.codeLeak.repo_name + '/blob/' + me.codeLeak.html_url_blob + '/' + me.codeLeak.path;
                        window.location.href = url;
                        break;
                }
            },
            updateStatus(status) {
                let me = this;
                axios.put('/api/codeLeak/' + me.codeLeak.id, {status: status}).then(function (rsp) {
                    if (rsp.data.success) {
                        me.codeLeak.status = status;
                        me.$toast.success('操作成功！');
                    } else {
                        me.$toast.fail(rsp.data.message);
                    }
                }).catch(function (rsp) {
                    me.$toast.fail(rsp);
                });
            },
        },
        mounted: function () {
            this.load();
        }
    });
</script>
</html>
