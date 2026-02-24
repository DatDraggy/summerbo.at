<script lang="ts">
  import { onMount } from 'svelte';
  import Chart from 'chart.js/auto';
  import Accordion from './components/Accordion.svelte';

  interface Attendee {
    nickname: string;
    rank: number;
    sponsor: boolean;
    benefactor: boolean;
    party: number;
    boat: number | null;
  }

  interface Stats {
    countries: Record<string, number>;
    ageGroups: Record<string, number>;
    fursuiters: number;
    nonFursuiters: number;
    vips: number;
    nonVips: number;
  }

  let attendees: Attendee[] = [];
  let stats: Stats | null = null;
  let isLoading = true;
  let error: string | null = null;

  onMount(async () => {
    try {
      const response = await fetch('https://api.summerbo.at/attendees');
      const data = await response.json();
      
      if (data && data.attendees && data.stats) {
        attendees = data.attendees;
        stats = data.stats;
      } else {
        throw new Error('Invalid data format received from API. Expected { attendees: [], stats: {} }');
      }
    } catch (e: any) {
      error = e.message;
      console.error('Error fetching attendees:', e);
    } finally {
      isLoading = false;
    }
  });

  const colors = ['rgba(254,218,33, 1)', 'rgb(244,136,137)', 'rgb(57,87,137)', 'rgb(62,193,199)'];

  function chartAction(node: HTMLCanvasElement, config: any) {
    const chart = new Chart(node, config);
    return {
      destroy() {
        chart.destroy();
      }
    };
  }

  function getCountryData() {
    if (!stats) return {};
    const sorted = Object.entries(stats.countries).sort((a, b) => b[1] - a[1]);
    return {
      type: 'bar' as const,
      data: {
        labels: sorted.map(c => c[0]),
        datasets: [{
          label: 'Attendees',
          data: sorted.map(c => c[1]),
          backgroundColor: sorted.map((_, i) => colors[i % colors.length])
        }]
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } } 
      }
    };
  }

  function getAgeData() {
    if (!stats) return {};
    return {
      type: 'bar' as const,
      data: {
        labels: Object.keys(stats.ageGroups),
        datasets: [{
          label: 'Attendees',
          data: Object.values(stats.ageGroups),
          backgroundColor: Object.keys(stats.ageGroups).map((_, i) => colors[i % colors.length])
        }]
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } } 
      }
    };
  }

  function getFursuitData() {
    if (!stats) return {};
    return {
      type: 'pie' as const,
      data: {
        labels: ['Fursuit', 'No Fursuit'],
        datasets: [{
          data: [stats.fursuiters, stats.nonFursuiters],
          backgroundColor: ['rgb(244,136,137)', 'rgb(57,87,137)']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    };
  }

  function getVipData() {
    if (!stats) return {};
    return {
      type: 'pie' as const,
      data: {
        labels: ['VIP', 'No VIP'],
        datasets: [{
          data: [stats.vips, stats.nonVips],
          backgroundColor: ['rgb(244,136,137)', 'rgb(57,87,137)']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    };
  }

  function getRank(a: Attendee) {
    if (a.rank >= 2) return 'Staff';
    if (a.rank === 1) return 'Crew';
    if (a.sponsor) return 'VIP';
    if (a.benefactor) return 'Benefactor';
    return null;
  }

  $: party1Attendees = attendees.filter(a => a.party === 1).sort((a, b) => {
    if (a.benefactor !== b.benefactor) return a.benefactor ? -1 : 1;
    return a.nickname.localeCompare(b.nickname);
  });
  $: party2Tunes = attendees.filter(a => a.party === 2 && a.boat === 1).sort((a, b) => {
    if (a.benefactor !== b.benefactor) return a.benefactor ? -1 : 1;
    return a.nickname.localeCompare(b.nickname);
  });
  $: party2Talky = attendees.filter(a => a.party === 2 && a.boat === 2).sort((a, b) => {
    if (a.benefactor !== b.benefactor) return a.benefactor ? -1 : 1;
    return a.nickname.localeCompare(b.nickname);
  });
</script>

<div class="text-content">
  <h2 class="text-headline">Attendee List</h2>

  {#if isLoading}
    <p>Loading attendee data...</p>
  {:else if error}
    <p class="error-message">Error: {error}</p>
  {:else}
    <h2 class="text-headline-line">Statistics</h2>
    
    <div class="stats-container">
      <Accordion question="Attendance by Country">
        <div class="chart-wrapper">
          <canvas use:chartAction={getCountryData()}></canvas>
        </div>
      </Accordion>

      <Accordion question="Attendance by Age">
        <div class="chart-wrapper">
          <canvas use:chartAction={getAgeData()}></canvas>
        </div>
      </Accordion>

      <div class="pie-stats">
        <Accordion question="Attendee to Fursuiter Ratio">
          <div class="chart-wrapper pie">
            <canvas use:chartAction={getFursuitData()}></canvas>
          </div>
        </Accordion>

        <Accordion question="Attendee to VIP Ratio">
          <div class="chart-wrapper pie">
            <canvas use:chartAction={getVipData()}></canvas>
          </div>
        </Accordion>
      </div>
    </div>

    <h2 class="text-headline-line">Attendees</h2>

    <Accordion question="__PARTY_SLOGAN__: Party">
      <div class="attendee-list">
        <ul>
          {#each party1Attendees as attendee}
            <li>
              {attendee.nickname}
              {#if getRank(attendee)}
                <span class="rank">{getRank(attendee)}</span>
              {/if}
            </li>
          {/each}
        </ul>
      </div>
    </Accordion>

    <Accordion question="__PARTY_SLOGAN__: Chill - Boat Tunes">
      <div class="attendee-list">
        <ul>
          {#each party2Tunes as attendee}
            <li>
              {attendee.nickname}
              {#if getRank(attendee)}
                <span class="rank">{getRank(attendee)}</span>
              {/if}
            </li>
          {/each}
        </ul>
      </div>
    </Accordion>

    <Accordion question="__PARTY_SLOGAN__: Chill - Boat Talky">
      <div class="attendee-list">
        <ul>
          {#each party2Talky as attendee}
            <li>
              {attendee.nickname}
              {#if getRank(attendee)}
                <span class="rank">{getRank(attendee)}</span>
              {/if}
            </li>
          {/each}
        </ul>
      </div>
    </Accordion>
  {/if}
</div>

<style>
  .rank {
    color: var(--color-text-secondary, #666);
    font-size: 0.8em;
    margin-left: 0.5em;
    text-transform: uppercase;
    font-weight: bold;
  }
  
  .attendee-list {
    margin-bottom: 3rem;
  }

  .attendee-list ul {
    list-style: none;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.5rem;
  }

  .attendee-list li {
    padding: 0.25rem 0;
  }

  .chart-wrapper {
    position: relative;
    height: 300px;
    width: 100%;
    margin-bottom: 2rem;
  }

  .chart-wrapper.pie {
    height: 250px;
    max-width: 400px;
    margin: 0 auto;
  }

  .pie-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }

  @media (max-width: 768px) {
    .pie-stats {
      grid-template-columns: 1fr;
    }
  }

  .stats-container {
    margin-bottom: 4rem;
  }
</style>
