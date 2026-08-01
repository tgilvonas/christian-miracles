<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { trans } from '@/helpers/translator'
import { route } from 'ziggy-js'
import { computed } from 'vue';

const page = usePage();
const currentUser = computed(() => (page.props.auth as { user?: { roles?: string[] } }).user ?? undefined);
const canManageUsers = computed(() => (currentUser.value?.roles ?? []).includes('ROLE_SUPERADMIN'));

const mainNavItems: NavItem[] = [
    {
        title: trans('dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: trans('miracles'),
        href: route('admin.miracles.index'),
        icon: LayoutGrid,
    },
    {
        title: trans('locations'),
        href: route('admin.locations.index'),
        icon: LayoutGrid,
    },
];

const visibleMainNavItems = computed<NavItem[]>(() => {
    const items = [...mainNavItems];

    if (canManageUsers.value) {
        items.push({
            title: trans('users'),
            href: route('admin.users.index'),
            icon: Users,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    /*
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
     */
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="visibleMainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
