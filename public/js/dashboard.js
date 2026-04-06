/* ============================================================
   STAP HUB — Dashboard JS
   Vehicle count trend chart + Open-Meteo weekly forecast
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // --------------------------------------------------------
    // 1. Vehicle Count Trend — ApexCharts area chart
    // --------------------------------------------------------
    if (document.getElementById('chart-trend') && STAP.trendData.length > 0) {

        const labels = STAP.trendData.map(d => d.date);
        const values = STAP.trendData.map(d => d.total);

        const trendChart = new ApexCharts(document.getElementById('chart-trend'), {
            chart: {
                type: 'area',
                height: 220,
                sparkline: { enabled: false },
                toolbar: { show: false },
            },
            series: [{
                name: 'Total Vehicles',
                data: values,
            }],
            xaxis: {
                categories: labels,
                labels: {
                    style: { fontSize: '11px', colors: '#A0AABF' },
                },
                axisBorder: { show: false },
                axisTicks:  { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#A0AABF' },
                    formatter: val => val.toLocaleString(),
                },
            },
            colors: ['#1B2744'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.25,
                    opacityTo: 0.02,
                    stops: [0, 100],
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2.5,
            },
            markers: {
                size: 4,
                colors: ['#fff'],
                strokeColors: '#1B2744',
                strokeWidth: 2,
                hover: { size: 6 },
            },
            tooltip: {
                y: { formatter: val => val.toLocaleString() + ' vehicles' },
            },
            grid: {
                borderColor: '#E8ECF4',
                strokeDashArray: 4,
                padding: { left: 10, right: 10 },
            },
            dataLabels: { enabled: false },
        });

        trendChart.render();
    }

    // --------------------------------------------------------
    // 2. Open-Meteo Weekly Weather Forecast
    //    Quezon City: lat 14.6760, lon 121.0437
    //    Shows Monday–Sunday of the current week
    // --------------------------------------------------------
    (function loadForecast() {

        const wrap = document.getElementById('weather-forecast-wrap');
        if (!wrap) return;

        // Compute Monday–Sunday of the current week
        function getWeekRange() {
            const now    = new Date();
            const day    = now.getDay(); // 0=Sun, 1=Mon...
            const diff   = (day === 0) ? -6 : 1 - day;
            const monday = new Date(now);
            monday.setDate(now.getDate() + diff);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);

            const fmt = d => d.toISOString().split('T')[0];
            return { start: fmt(monday), end: fmt(sunday) };
        }

        // WMO weather code → emoji + label
        function weatherIcon(code, precip) {
            if (precip > 10) return { icon: '⛈️',  label: 'Storm'  };
            if (precip > 3)  return { icon: '🌧️',  label: 'Rain'   };
            if (precip > 0)  return { icon: '🌦️',  label: 'Showers'};
            if (code === 0)  return { icon: '☀️',   label: 'Clear'  };
            if (code <= 3)   return { icon: '⛅',   label: 'Cloudy' };
            if (code <= 48)  return { icon: '🌫️',   label: 'Foggy'  };
            if (code <= 67)  return { icon: '🌧️',  label: 'Rain'   };
            if (code <= 77)  return { icon: '🌨️',   label: 'Snow'   };
            if (code <= 82)  return { icon: '🌧️',  label: 'Rain'   };
            return { icon: '⛈️', label: 'Storm' };
        }

        const { start, end } = getWeekRange();
        const todayStr = new Date().toISOString().split('T')[0];

        const url = `https://api.open-meteo.com/v1/forecast`
            + `?latitude=14.6326863&longitude=121.1013367`//Mayyor Fil Fernando Ave, Marikina
            + `&daily=weathercode,temperature_2m_max,precipitation_sum`
            + `&start_date=${start}&end_date=${end}`
            + `&timezone=Asia%2FManila`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                const days   = data.daily.time;
                const codes  = data.daily.weathercode;
                const temps  = data.daily.temperature_2m_max;
                const precip = data.daily.precipitation_sum;

                const dowLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

                let html = '<div class="dash-forecast-grid">';

                days.forEach(function (dateStr, i) {
                    const d        = new Date(dateStr + 'T00:00:00');
                    const dow      = dowLabels[d.getDay()];
                    const isToday  = dateStr === todayStr;
                    const weather  = weatherIcon(codes[i], precip[i]);
                    const rainMm   = precip[i] > 0
                        ? `<span class="dash-forecast-rain has-rain">${precip[i].toFixed(1)} mm</span>`
                        : `<span class="dash-forecast-rain">No rain</span>`;

                    html += `
                        <div class="dash-forecast-day ${isToday ? 'today' : ''}">
                            <span class="dash-forecast-dow">${isToday ? 'Today' : dow}</span>
                            <span class="dash-forecast-icon" title="${weather.label}">${weather.icon}</span>
                            <span class="dash-forecast-temp">${Math.round(temps[i])}°C</span>
                            ${rainMm}
                        </div>`;
                });

                html += '</div>';
                wrap.innerHTML = html;
            })
            .catch(function () {
                wrap.innerHTML = '<p class="dash-forecast-error">Weather forecast unavailable. Check your connection.</p>';
            });
    })();

});