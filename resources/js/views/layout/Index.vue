<template>
    <a-layout :style="{ height:'100%' }">
        <a-layout-header>
            <div class="logo"/>
            <a-menu theme="dark" mode="horizontal" :selectedKeys="[this.$route.path]" :style="{ lineHeight: '64px' }">
                <a-menu-item key="/home">
                    <router-link to="/home">
                        <a-icon type="home"/>
                        首页概况
                    </router-link>
                </a-menu-item>
                <a-menu-item key="/codeLeak">
                    <router-link to="/codeLeak">
                        <a-icon type="security-scan"/>
                        扫描结果
                    </router-link>
                </a-menu-item>
                <a-menu-item key="/configToken">
                    <router-link to="/configToken">
                        <a-icon type="safety-certificate"/>
                        令牌配置
                    </router-link>
                </a-menu-item>
                <a-menu-item key="/configJob">
                    <router-link to="/configJob">
                        <a-icon type="setting"/>
                        任务配置
                    </router-link>
                </a-menu-item>
                <a-menu-item key="/configWhitelist">
                    <router-link to="/configWhitelist">
                        <a-icon type="unordered-list"/>
                        扫描白名单
                    </router-link>
                </a-menu-item>
            </a-menu>
        </a-layout-header>

        <a-layout-content :style="{ padding: '40px 50px 0', height: '100%' }">
            <div :style="{ padding: '24px', height: '100%', background: '#FFF' }">
                <router-view/>
            </div>
        </a-layout-content>

        <a-layout-footer :style="{ position: 'absolute', bottom: '0', width: '100%', textAlign: 'center'}">
            Copyright © 2020 码小六 All Right Reserved
        </a-layout-footer>
    </a-layout>
</template>

<script>
    import {Layout, Menu, Icon, ConfigProvider} from 'ant-design-vue';

    export default {
        components: {
            'ALayout': Layout,
            'ALayoutHeader': Layout.Header,
            'ALayoutContent': Layout.Content,
            'ALayoutFooter': Layout.Footer,
            'AMenu': Menu,
            'AMenuItem': Menu.Item,
            'AIcon': Icon,
            'AConfigProvider': ConfigProvider,
        }
    }
</script>

<style>
    .logo {
        width: 120px;
        height: 32px;
        background: rgba(255, 255, 255, 0.2);
        margin: 16px 24px 16px 0;
        float: left;
    }
</style>
