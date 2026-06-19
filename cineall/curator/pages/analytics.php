<?php
/**
 * ============================================================================
 * ANALYTICS - Stats and insights
 * ============================================================================
 */

require_once __DIR__ . '/../php/bootstrap.php';

$current_page = 'analytics';

include '../includes/header.php';
?>

<div id="app">
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include '../includes/top-header.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div class="page-kicker">Insights</div>
        <h1 class="page-title">Analytics</h1>
        <p class="page-subtitle">
          View engagement metrics, trending content, and audience insights.
        </p>
      </div>

      <div class="kpi-grid">
        <div class="card">
          <div class="kpi-label">Page views (7d)</div>
          <div class="kpi-value">18.4<span class="kpi-suffix">k</span></div>
          <div class="kpi-delta up">▲ 8% vs previous week</div>
        </div>

        <div class="card">
          <div class="kpi-label">Avg. time on site</div>
          <div class="kpi-value">4<span class="kpi-suffix">m 22s</span></div>
          <div class="kpi-delta up">▲ 15% vs previous week</div>
        </div>

        <div class="card">
          <div class="kpi-label">Top film</div>
          <div class="kpi-value" style="font-size: 24px">Vessel</div>
          <div class="kpi-delta" style="color: var(--amuted)">2,341 views this week</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Most Viewed Films (Last 7 Days)</h3>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Film</th>
              <th>Views</th>
              <th>Change</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><strong>Vessel</strong></td>
              <td>2,341</td>
              <td><span class="badge badge-good">▲ 23%</span></td>
            </tr>
            <tr>
              <td>2</td>
              <td><strong>Concrete Garden</strong></td>
              <td>1,892</td>
              <td><span class="badge badge-good">▲ 18%</span></td>
            </tr>
            <tr>
              <td>3</td>
              <td><strong>Hum</strong></td>
              <td>1,654</td>
              <td><span class="badge badge-accent">▲ 5%</span></td>
            </tr>
            <tr>
              <td>4</td>
              <td><strong>Northwind</strong></td>
              <td>1,432</td>
              <td><span class="badge badge-accent">▲ 3%</span></td>
            </tr>
            <tr>
              <td>5</td>
              <td><strong>The Quiet Hour</strong></td>
              <td>1,289</td>
              <td><span class="badge badge-neutral">— 0%</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
