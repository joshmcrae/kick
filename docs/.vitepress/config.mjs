import { defineConfig } from 'vitepress'

export default defineConfig({
    title: "Kick PHP",
    description: "A fresh framework with the essentials",
    base: '/kick',
    themeConfig: {
        logo: 'logo.png',
        nav: [
            { text: 'Home', link: '/' },
            { text: 'Guide', link: '/introduction' }
        ],
        sidebar: [
            {
                text: 'Introduction',
                items: [
                    { text: 'Why Kick?', link: '/introduction' },
                    { text: 'Getting Started', link: '/getting-started' }
                ]
            }
        ],
        socialLinks: [
            { icon: 'github', link: 'https://github.com/joshmcrae/kick' }
        ]
    }
})
