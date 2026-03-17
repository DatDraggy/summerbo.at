<script>
    import {formatDate, formatTime} from "../../helper/date.js";

    export let departTime;
    const actualDepartTime = new Date(departTime);
    actualDepartTime.setUTCMinutes(actualDepartTime.getUTCMinutes() + 30);

    const dateString = formatDate(departTime, {month: 'long', day: 'numeric', year: 'numeric'});
    const timeString = formatTime(departTime, {hour: '2-digit', minute: '2-digit'});
    const actualDepartTimeString = formatTime(actualDepartTime, {hour: '2-digit', minute: '2-digit'});
</script>

<p>
    nu geiht dat in de vierde Runnd, min Jung! In Hamburg, we'll be the first to party before we dive into the sea of fur at Eurofurence!
    Just like the previous years, we'll again be boarding the great and grand MS Hamburg at Überseebrücke on the Elbe with countless party-furs on {dateString} at {timeString} and depart at {actualDepartTimeString} to party all
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
