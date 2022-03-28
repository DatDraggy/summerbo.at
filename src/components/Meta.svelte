<script>
    import {MetaTags} from 'svelte-meta-tags';

    export let title = 'All Paws on Deck: Furry Summerbo.at Party';
    export let description = 'Summerbo.at is a super awesome furry party, on a boat, in Berlin, at Eurofurence! 23 August 2022';
    export let url = 'https://summerbo.at';
</script>

<MetaTags
    title={title}
    description={description}
    canonical={url}
    openGraph={
        {
            url: 'https://summerbo.at',
            title: {title},
            description: {description},
            type: 'website',
            images: [
              {
                url: 'https://summerbo.at/images/og-image.jpg',
                width: 1415,
                height: 741,
                alt: 'Logo'
              },
            ],
            site_name: 'summerbo.at'
        }
    }
    twitter={
        {
            handle: '@summerbo_at',
            site: '@summerbo_at',
            cardType: 'summary',
            title: 'All Paws on Deck 2022',
            description: 'All Paws on Deck 2022 — Summerbo.at is a super awesome furry party, on a boat, in Berlin, at Eurofurence! 23 August 2022',
            image: 'https://summerbo.at/images/og-image.jpg',
            imageAlt: 'Summerbo.at Logo'
        }
   }
/>