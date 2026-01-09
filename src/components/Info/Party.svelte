<script>
    export let departTime;
    const actualDepartTime = new Date(departTime);
    actualDepartTime.setMinutes(actualDepartTime.getMinutes() + 30);
</script>

<p>
    drei ma hoch uff Hamburch! When it's time for Eurofurence, we'll be there first!
    Just like the previous years, we'll again be boarding the great and grand MS Hamburg at Überseebrücke on the Elbe with countless party-furs on {departTime.toLocaleDateString('en-US', {month: 'long'})} {departTime.getDate()}, {departTime.getFullYear()} at {departTime.toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})} and depart at {actualDepartTime.toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})} to party all
    evening, making a round trip on the Elbe through Hamburg with live music on the TWO outer decks.
</p>
<div class="pricing">
    <div class="pricing-unit">
        <h3>Standard Ticket &mdash; 40&euro;</h3>
        <ul>
            <li>Access to cabin and amenities</li>
        </ul>
    </div>
    <div class="pricing-unit vip">
        <h3>VIP Upgrade &plus; 25&euro;</h3>
        <ul>
            <li>Support the party</li>
            <li>One exclusive gift</li>
        </ul>
    </div>
</div>
<p>
    Our ship in Hamburg is <em>even</em> <strong>larger than the one in Berlin or the first one in Hamburg, with more decks for more room,
    more modern, equipped with AC and colorful lighting</strong>.
    As last time, we'll have <strong>multiple fully stocked bars</strong> with
    plenty of cider, a larger
    <strong>fursuit lounge with cooling</strong> and live
    <strong>DJs for music inside <em>and</em> outside</strong>.
</p>
