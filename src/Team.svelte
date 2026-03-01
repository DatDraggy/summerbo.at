<script lang="ts">
  export let members = [
    {
      name: "Hunter",
      role: "Organizer",
      img: "img/staff/hunter.png",
      twitter: "hunterhuskynl",
      description:
        "Curious husky that loves to organize stuff. Originally from Netherlands, used to live in Berlin, Germany, now back in the Netherlands. Started with organizing the Furrave for many years, now expanding to Berlin and Germany. Loves gaming, fursuits and parties!",
    },
    {
      name: "Kieran",
      role: "IT and Registration",
      img: "img/staff/kieran.png",
      twitter: "DatDraggy",
      description:
        "I'm literally a dragon, like, actually in real life. I do computer things in my free time and, obviously, code a lot. This draggo is making sure that everything technical works smoothly. ",
    },
    {
      name: "Cubitus",
      role: "Operations",
      img: "img/staff/cubitus.png",
      twitter: "cubitus",
      description:
        "Half dog half werewulf, living in Berlin and loves to organize things for (not only) furries. Critical and empathetic thinker",
    },
    {
      name: "BerkWolf",
      role: "Financials",
      img: "img/staff/berkwolf.png",
      twitter: "cubitus",
      description:
        "A German black wolf from Stuttgart who can speak many languages if given cider. Wise and smart but loves to play tricks on people, but never with money.",
    },
    {
      name: "Vaelken",
      role: "Documentation",
      description: "",
    },
  ];
</script>

<h2 class="text-headline">Team</h2>

<div class="team-grid">
  {#each members as member}
    <div class="team-member">
      <h3 class="text-highlight">{member.name}</h3>
      <h4>{member.role}</h4>
      {#if member.img}
        <img
          src={member.img}
          style="display: block; width: 4rem; border-radius: 50%; margin-bottom:2rem;margin-top:1rem;"
          alt={`Photo of ${member.name}`}
        />
      {:else}<div style="height: 4rem; margin-top:1rem; margin-bottom:2rem"></div>
      {/if}

      <p>
        {member.description}
      </p>
    </div>
  {/each}
</div>
