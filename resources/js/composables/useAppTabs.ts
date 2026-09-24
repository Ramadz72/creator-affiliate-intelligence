import { ref } from 'vue';

export type AppTab = {
    id: string;
    title: string;
    href: string | null;
    closable: boolean;
};

const defaultTab: AppTab = {
    id: 'dashboard',
    title: 'Dashboard',
    href: '/dashboard',
    closable: false,
};

const tabs = ref<AppTab[]>([defaultTab]);
const activeTabId = ref('dashboard');

let initialized = false;

const saveTabs = () => {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.setItem(
        'creator-affiliate-tabs',
        JSON.stringify(tabs.value),
    );

    localStorage.setItem(
        'creator-affiliate-active-tab',
        activeTabId.value,
    );
};

const initializeTabs = (currentUrl?: string) => {
    if (initialized || typeof window === 'undefined') {
        return;
    }

    initialized = true;

    try {
        const savedTabs = localStorage.getItem(
            'creator-affiliate-tabs',
        );

        if (savedTabs) {
            const parsed = JSON.parse(savedTabs) as AppTab[];

            if (Array.isArray(parsed)) {
                const savedDashboard = parsed.find(
                    (tab) => tab.id === 'dashboard',
                );

                const otherTabs = parsed.filter(
                    (tab) => tab.id !== 'dashboard',
                );

                tabs.value = [
                    savedDashboard ?? defaultTab,
                    ...otherTabs,
                ];
            }
        }

        const savedActiveTab = localStorage.getItem(
            'creator-affiliate-active-tab',
        );

        if (
            savedActiveTab &&
            tabs.value.some((tab) => tab.id === savedActiveTab)
        ) {
            activeTabId.value = savedActiveTab;
        }

        if (currentUrl) {
            const matchingTab = tabs.value
                .slice()
                .reverse()
                .find(
                    (tab) =>
                        tab.href &&
                        (
                            currentUrl === tab.href ||
                            currentUrl.startsWith(`${tab.href}/`)
                        ),
                );

            if (matchingTab) {
                activeTabId.value = matchingTab.id;
            }
        }

        saveTabs();
    } catch {
        tabs.value = [defaultTab];
        activeTabId.value = 'dashboard';
    }
};

const createTab = () => {
    const newTab: AppTab = {
        id: crypto.randomUUID(),
        title: 'Tab Baru',
        href: null,
        closable: true,
    };

    tabs.value.push(newTab);
    activeTabId.value = newTab.id;

    saveTabs();

    return newTab;
};

const updateActiveTab = (
    title: string,
    href: string,
) => {
    const tab = tabs.value.find(
        (item) => item.id === activeTabId.value,
    );

    if (!tab) {
        return;
    }

    tab.title = title;
    tab.href = href;

    saveTabs();
};

const updateActiveTabUrl = (href: string) => {
    const tab = tabs.value.find(
        (item) => item.id === activeTabId.value,
    );

    if (!tab) {
        return;
    }

    tab.href = href;

    saveTabs();
};



const activateTab = (id: string) => {
    if (!tabs.value.some((tab) => tab.id === id)) {
        return;
    }

    activeTabId.value = id;

    saveTabs();
};

const closeTab = (id: string) => {
    if (id === 'dashboard') {
        return;
    }

    const index = tabs.value.findIndex(
        (tab) => tab.id === id,
    );

    if (index === -1) {
        return null;
    }

    const wasActive = activeTabId.value === id;

    tabs.value.splice(index, 1);

    let nextTab: AppTab | null = null;

    if (wasActive) {
        nextTab =
            tabs.value[index] ??
            tabs.value[index - 1] ??
            defaultTab;

        activeTabId.value = nextTab.id;
    }

    saveTabs();

    return nextTab;
};

const getActiveTab = () => {
    return tabs.value.find(
        (tab) => tab.id === activeTabId.value,
    );
};

export function useAppTabs() {
    return {
        tabs,
        activeTabId,
        initializeTabs,
        createTab,
        updateActiveTab,
        updateActiveTabUrl,
        activateTab,
        closeTab,
    };
}