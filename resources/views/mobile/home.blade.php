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
    <!-- 头部开始 -->
    <div class="header">
        <div class="logo"></div>
    </div>
    <!-- 头部结束 -->

    <van-divider></van-divider>

    <!-- 列表开始 -->
    <van-pull-refresh v-model="loading" success-text="加载成功" @refresh="load(page.current)">
        <van-list>
            <template v-if="list.data && list.data.length > 0">
                <van-cell class="item" v-for="item in list.data" @click="showAction(item)">
                    <template #title>
                        <span v-html="formatStatus(item.status)"></span><b class="title">[[item.created_at]]</b>
                    </template>

                    <template #label>
                        关键字：[[item.keyword]]
                        <br/>
                        仓　库：[[item.repo_owner]]/[[item.repo_name]]
                        <br/>
                        文　件：[[item.path]]
                        <br/>
                        描　述：<span v-if="item.repo_description">[[item.repo_description]]</span><span v-else>无</span>
                    </template>
                </van-cell>
            </template>

            <van-empty v-else-if="!loading" description="暂 无 数 据"></van-empty>
        </van-list>
    </van-pull-refresh>
    <!-- 列表结束 -->

    <!-- 分页开始 -->
    <van-pagination v-show="page.count>1" @change="load(page.current)" v-model="page.current" :page-count="page.count">
        <template #prev-text>
            <van-icon name="arrow-left"></van-icon>
        </template>
        <template #next-text>
            <van-icon name="arrow"></van-icon>
        </template>
    </van-pagination>
    <!-- 分页结束 -->

    <!-- 菜单开始 -->
    <van-action-sheet v-model="action.show" :actions="action.data" @select="onActionSelect"></van-action-sheet>
    <!-- 菜单结束 -->

    <!-- 标签开始 -->
    <van-tabbar v-model="tab.current" active-color="#1890FF" @change="load(1)">
        <van-tabbar-item name="all" icon="more-o">全部</van-tabbar-item>
        <van-tabbar-item name="0" icon="question-o">未审</van-tabbar-item>
        <van-tabbar-item name="1" icon="close">误报</van-tabbar-item>
        <van-tabbar-item name="2" icon="warning-o">异常</van-tabbar-item>
        <van-tabbar-item name="3" icon="passed">解决</van-tabbar-item>
    </van-tabbar>
    <!-- 标签结束 -->
</div>
</body>
<script>
    new Vue({
        el: '#app',
        delimiters: ['[[', ']]'],
        data: {
            loading: true,
            page: {
                count: {{ $count }},
                current: {{ $page }},
            },
            action: {
                show: false,
                data: [
                    {name: '设为未审', value: 0, color: '#738C99'},
                    {name: '设为误报', value: 1, color: '#409EFF'},
                    {name: '设为异常', value: 2, color: '#F56C6C'},
                    {name: '设为解决', value: 3, color: '#67C23A'},
                    {name: '查看代码', value: 'view-source'},
                ]
            },
            list: {
                data: [],
                selection: [],
            },
            tab: {
                current: '{{ $tab }}',
            }
        },
        methods: {
            load(page) {
                var me = this;
                scrollTo(0, 0);
                me.loading = true;
                var params = {page: page ? page : 1, limit: 10};
                if (me.tab.current !== 'all') {
                    params.status = me.tab.current;
                }
                axios.get('/api/codeLeak', {params: params}).then(function (rsp) {
                    me.page.current = page;
                    me.page.count = rsp.data.last_page ? rsp.data.last_page : 0;
                    me.list.data = rsp.data.data;
                    me.loading = false;
                    history.pushState({
                        page: me.page,
                        data: me.list.data,
                        tab: me.tab,
                    }, '', '/mobile?page=' + page + '&tab=' + me.tab.current);
                }).catch(function (rsp) {
                    me.$toast.fail(rsp.message);
                    me.loading = false;
                });
            },
            showAction(item) {
                this.list.selection = item;
                this.action.show = true;
            },
            formatStatus(status) {
                var conf = [
                    {color: '#738C99', text: '未审'},
                    {color: '#409EFF', text: '误报'},
                    {color: '#F56C6C', text: '异常'},
                    {color: '#67C23A', text: '解决'},
                ];
                return `<div class="tag" style="background:${conf[status].color}">${conf[status].text}</div>`;
            },
            onActionSelect(item) {
                var me = this;
                me.action.show = false;
                switch (item.value) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                        me.update({status: item.value});
                        break;
                    case 'view-source':
                        var s = me.list.selection;
                        var url = `https://github.com/${s.repo_owner}/${s.repo_name}/blob/${s.html_url_blob}/${s.path}`;
                        window.location.href = url;
                        break;
                }
            },
            update(data) {
                var me = this;
                var item = me.list.selection;
                axios.put('/api/codeLeak/' + item.id, data).then(function (rsp) {
                    if (rsp.data.success) {
                        item = Object.assign(item, data);
                        me.$toast.success('操作成功');
                    } else {
                        me.$toast.fail(rsp.data.message);
                    }
                }).catch(function (rsp) {
                    me.$toast.fail(rsp.message);
                });
            },
        },
        mounted: function () {
            var me = this;
            me.load(me.page.current);
            window.addEventListener('popstate', function (e) {
                if (e.state && e.state.data) {
                    me.page = e.state.page;
                    me.list.data = e.state.data;
                    me.tab = e.state.tab;
                    me.loading = false;
                }
            })
        }
    });
</script>
</html>
