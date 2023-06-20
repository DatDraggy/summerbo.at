<script lang="ts">
  const benefactors = [
    {
      name: 'Anon',
      message: 'uwu',
      img: "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=",
    },
  ];
</script>

<div class="text-content">
  <h2 class="text-headline">Benefactors</h2>

  <div class="benefactor-container">
    {#each benefactors as benefactor, i }
      <div class="benefactor">
        <div class="benefactor-title">
          <img alt="{benefactor.name} Logo" class="benefactor-img" src="{benefactor.img}">

          <div class="benefactor-name">{benefactor.name}</div>
        </div>
        <div class="benefactor-message">{benefactor.message}</div>
      </div>
    {/each}
  </div>

  <h2 class="text-headline-line">
    The Benefactor Package
  </h2>

  <p>
    As a relatively small, yearly event that needs to keep itself afloat, we don't have a lot to offer.<br>
    Our more than grateful "Thank-you" includes:
  </p>

  <ul class="textList">
    <li>Named or anonymous mention on the website</li>
    <li>Optional short free text accompanying your mention (Ad for your furry business etc)</li>
    <li>Early-access boarding</li>
    <li>Free welcome drink</li>
  </ul>

  <h2 class="text-headline-line">Why?</h2>
  <p>
    As you might've experienced yourself as an attendee, a change in the location of an event is not a simple task for both sides.<br>
    Our move to Hamburg has been quite the endeavour. New business partners, schedules, floor plans, logistics, contracts, etc can add up to quite a lot of work.<br>
    Luckily, we've managed to set it up regardless of the challenges we encountered. However, at a much greater cost than we expected, because we had to open registration with set prices soon so people could adapt their travelling plans accordingly.<br>
  </p>

  <p>
    It was important to us to be able to provide a party that didn't have to raise their ticket prices, as everything else around Eurofurence already became a little more pricey.<br>
    To support our finances, we instead raised the VIP upgrade by 5€.<br>
    Naturally, without knowing the number of attendees and upgrades beforehand, it made calculating not an easy task.<br>
    Now that we're gratefully sold out, but with VIP upgrades lacking behind, we've had to make a decision.
  </p>

  <p>
    This years party will happen, no doubt in that, however to be able to maintain and increase the quality of our event for this and the following years, a financial padding is a good thing to have.<br>
    You're probably guessing where this is going.
  </p>

  <p>
    Despite saving costs where we could, we're running a bit short on funds.
    We therefor made the decision to add an exclusive additional package for this year only because luckily, there's people who have the funds and aren't shy to use them on their yearly events they attend to help them succeed even more.
  </p>

  <p>
    We (the organizers) do NOT have direct access to our funds. Ticket/Upgrade sales are solely paid to and managed by Eurofurence and can only be used to pay the events bills. Any income we receive will be used for this and the following parties exclusively.
  </p>

</div>