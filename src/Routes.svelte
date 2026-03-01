<script lang="ts">
    import { Route } from "tinro/cmp";
    import Privacy from './Privacy.svelte';
    import Conduct from "./Conduct.svelte";
    import Team from "./Team.svelte";
    import BackToTopButton from "./BackToTopButton.svelte";
    import TwentyNineteen from "./2019.svelte";
    import TwentyTwentyTwo from "./2022.svelte";
    import TwentyTwentyThree from "./2023.svelte";
    import TwentyTwentyFour from "./2024.svelte";
    import TwentyTwentyFive from "./2025.svelte";
    import Tos from "./Tos.svelte";
    import Faq from "./Faq.svelte";
    import NotFound from "./NotFound.svelte";
    import Default from "./Default.svelte";
    import Glympse from "./Glympse.svelte"
    import Benefactors from "./Benefactors.svelte";
    import Attendees from "./Attendees.svelte";
    import Flow from "./Registration/Flow.svelte";
    import Confirm from "./Registration/Confirm.svelte";
    import Selector from "./components/Info/Selector.svelte";

    export let departTime: Date;
    export let header: HTMLElement;
</script>

<div class="content-wrapper">
    <Route path="/">
        <div class="text-content">
            <h2 class="text-headline">
                Load up your confetti canons, it's&hellip;<br/><span
                    class="color-secondary">__PARTY_SLOGAN__ {departTime.getFullYear()}!</span
            >
            </h2>

            <Selector {departTime}/>

            <p>
                Check out our <a href="/attendees">attendees</a>!
            </p>
            <p>
                Made possible by our lovely 2023
                <a href="/benefactors">Benefactors</a>
                and current VIPs!
            </p>
            <p>
                Registration opens on {new Date("__REG_ISO_DATE__T20:00:00+01:00").toLocaleDateString('en-US', {
                day: "numeric",
                month: "short"
            })}.
                Follow us on
                <a href="https://bsky.app/profile/summerbo.at">Bsky</a>
                and join our <a href="https://t.me/summerboatinfo">Telegram</a> channel to be reminded!
            </p>
        </div>
    </Route>
    <Route path="/privacy"><Privacy/></Route>
    <Route path="/conduct"><Conduct/></Route>
    <Route path="/tos"><Tos/></Route>
    <Route path="/team"><Team/></Route>
    <Route path="/faq"><Faq/></Route>
    <Route path="/glympse"><Glympse/></Route>
    <Route path="/benefactors"><Benefactors/></Route>
    <Route path="/register">
        <Flow {departTime} />
    </Route>
    <Route path="/confirm"><Confirm/></Route>
    <Route path="/login" redirect="/register"/>
    <Route path="/badge" redirect="/"/>
    <Route path="/attendees"><Attendees/></Route>
    <Route path="/contact" redirect="https://reg.summerbo.at/contact"/>

    <Route path="/archive" redirect="/archive/2019"/>
    <Route path="/archive/2019"><TwentyNineteen/></Route>
    <Route path="/archive/2022"><TwentyTwentyTwo/></Route>
    <Route path="/archive/2023"><TwentyTwentyThree/></Route>
    <Route path="/archive/2024"><TwentyTwentyFour/></Route>
    <Route path="/archive/2025"><TwentyTwentyFive/></Route>

    <Route path="/404"><NotFound/></Route>
    <Route fallback><Default/></Route>
    <BackToTopButton scrollTo={header}/>
</div>
