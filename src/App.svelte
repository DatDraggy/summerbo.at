<script lang="ts">
    import Graphics from "./components/Graphics.svelte";
    import Timer from "./Timer.svelte";
    import Routes from "./Routes.svelte";

    let header;
    let departTime = new Date("__PARTY_ISO_DATE__T18:30:00+02:00");
    let regTime = new Date("__REG_ISO_DATE__T20:00:00+01:00");

    let timeTillReg = 1;
</script>

<main>
    <div class="banner">
        <div class="registration-banner">
            <a href="/">
                <Graphics type="hero" style="max-width:100%; margin-bottom:4rem"/>
            </a>
            <a
                    class="registration-button"
                    style="margin-bottom: 1rem; margin-top: 2rem;"
                    href="/register">Register Here</a
            >
            <p
                    style="font-size: 0.66rem; text-transform: uppercase; letter-spacing: 1.5px;"
            >
                {timeTillReg === 0 ? '' : `Starts at ${regTime.toLocaleDateString('en-US', {
                    day: "numeric",
                    month: "short",
                    year: "numeric"
                })} ${regTime.toLocaleTimeString('de-DE', {hour: "numeric", minute: "numeric"})}`}
            </p>
        </div>
        <video class="banner-video" muted autoplay loop>
            <source src="/img/bgoptim.webm" type="video/webm"/>
        </video>
    </div>
    <div class="content">
        <div class="header" bind:this={header}>
            <h1 class="logo">
      <span class="text-highlight"
      ><a href="/" style="color:inherit">Summerbo.at</a></span
      ><br/>
                Furry Boat Party
            </h1>
            <nav>
                <a href="/archive/2019">2019</a>
                <a href="/archive/2022">2022</a>
                <a href="/archive/2023">2023</a>
                <a href="/archive/2024">2024</a>
                <a href="/archive/2025">2025</a>
                <a href="/">__PARTY_YEAR__</a>
            </nav>
        </div>
        <Routes {departTime} {header}/>
        <footer class="footer">
            <Timer {departTime} {regTime} />
            <div class="footer-section">
                <h3 class="text-headline-line">Boarding</h3>
                <p>{departTime.toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})} {departTime.toLocaleDateString('en-US', {month: 'long'})} {departTime.getDate()}, {departTime.getFullYear()}<br/>Überseebrücke, Hamburg</p>
            </div>
            <div class="footer-section">
                <h3 class="text-headline-line">Details</h3>
                <nav class="footer-grid">
                    <ul>
                        <li class="socials">
                            <a href="mailto:contact@summerbo.at" class="social-link">
                                <Graphics
                                        type={"email"}
                                        style={"width: 24px; height: 24px"}
                                />
                            </a
                            >
                            <a href="https://t.me/summerboat" class="social-link"
                            >
                                <Graphics
                                        type={"telegram"}
                                        style={"width: 24px; height: 24px"}
                                />
                            </a
                            >
                            <a href="https://bsky.app/profile/summerbo.at" class="social-link">
                                <Graphics
                                        type={"bsky"}
                                        style={"width: 24px; height: 24px;"}
                                />
                            </a
                            >
                        </li>
                        <li>
                            <a href="/faq">FAQ</a>
                        </li>
                        <li>
                            <a href="/team">Team</a>
                        </li>
                        <li>
                            <a
                                    href="https://github.com/DatDraggy/summerbo.at"
                                    target="_blank">Source Code</a
                            >
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="/attendees">Attendees</a>
                        </li>
                        <li>
                            <a href="/privacy">Privacy</a>
                        </li>
                        <li>
                            <a href="/conduct">Conduct</a>
                        </li>
                        <li>
                            <a href="/tos">Terms of Service</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </footer>
    </div>
</main>

