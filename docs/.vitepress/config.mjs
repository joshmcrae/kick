import { defineConfig } from 'vitepress'

export default defineConfig({
    title: "Kick PHP",
    description: "A fresh framework with the essentials",
    base: '/kick',
    themeConfig: {
        logo: './logo.png',
        search: { provider: 'local' },
        nav: [
            { text: 'Home', link: '/' },
            { text: 'Guide', link: '/introduction' }
        ],
        sidebar: [
            {
                text: 'Introduction',
                items: [
                    { text: 'What is Kick?', link: '/introduction' },
                    { text: 'Getting Started', link: '/getting-started' }
                ]
            },
            {
                text: 'Concerns',
                items: [
                    { text: 'Routing', link: '/routing' },
                    { text: 'Dependency Injection', link: '/dependency-injection' },
                    { text: 'Views', link: '/views' }
                ]
            }
        ],
        socialLinks: [
            { icon: 'github', link: 'https://github.com/joshmcrae/kick' }
        ]
    }
})
