function initReadingsLive() {
	const tbody = document.getElementById('readings-body');
	if (!tbody) return;

	async function fetchAndRender() {
		try {
			const res = await fetch('get_latest_readings.php');
			if (!res.ok) throw new Error('Network response was not ok');
			const data = await res.json();
			tbody.innerHTML = '';
			if (!data || data.length === 0) {
				tbody.innerHTML = '<tr><td colspan="7">No readings yet</td></tr>';
				return;
			}
			data.forEach(row => {
				const tr = document.createElement('tr');
				tr.innerHTML = '<td>' + escapeHtml(row.id) + '</td>' +
											 '<td>' + escapeHtml(row.customer_name || '') + '</td>' +
											 '<td>' + escapeHtml(row.meter_number || '') + '</td>' +
											 '<td>' + escapeHtml(row.previous_reading) + '</td>' +
											 '<td>' + escapeHtml(row.current_reading) + '</td>' +
											 '<td>' + escapeHtml(row.consumption) + '</td>' +
											 '<td>' + escapeHtml(row.reading_date) + '</td>';
				tbody.appendChild(tr);
			});
		} catch (err) {
			console.error('Failed to fetch readings', err);
			tbody.innerHTML = '<tr><td colspan="7">Error loading readings</td></tr>';
		}
	}

	fetchAndRender();
	window.readingsInterval = setInterval(fetchAndRender, 10000);
}

function escapeHtml(str) {
	if (str === null || str === undefined) return '';
	return String(str).replace(/[&<>"']/g, function (m) {
		return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
	});
}

function initCustomersListAutoUpdate() {
	const elements = document.querySelectorAll('.latest-reading[data-customer-id]');
	if (!elements || elements.length === 0) return;

	async function updateAll() {
		try {
			const res = await fetch('get_latest_readings_all.php');
			if (!res.ok) throw new Error('Network response not ok');
			const data = await res.json();
			elements.forEach(el => {
				const id = el.getAttribute('data-customer-id');
				const r = data[id];
				if (r) {
					// prefer the explicit previous_reading on the latest row; otherwise use previous entry's current_reading
					const prev = (r.previous_reading !== null && r.previous_reading !== '') ? r.previous_reading : (r.previous_entry_current || 'N/A');
					const prevDate = r.previous_entry_date ? (' on ' + r.previous_entry_date) : '';
					el.textContent = 'Prev: ' + prev + prevDate + ' | Curr: ' + (r.current_reading || 'N/A') + ' (cons: ' + (r.consumption || '0') + ') on ' + (r.reading_date || '');
				} else {
					el.textContent = 'No reading';
				}
			});
		} catch (err) {
			console.error('Failed to update latest readings', err);
		}
	}

	updateAll();
	window.customersReadingsInterval = setInterval(updateAll, 10000);
}

// SSE client to update customers list in real-time; falls back to polling if EventSource not available
function initSSEForCustomers() {
	if (typeof EventSource === 'undefined') return;
	try {
		const es = new EventSource('readings_stream.php');
		es.onmessage = function(e) {
			try {
				const row = JSON.parse(e.data);
				// update any matching customer cell
				const el = document.querySelector('.latest-reading[data-customer-id="' + row.customer_id + '"]');
				if (el) {
					const prev = (row.previous_reading !== null && row.previous_reading !== '') ? row.previous_reading : (row.previous_entry_current || 'N/A');
					const prevDate = row.previous_entry_date ? (' on ' + row.previous_entry_date) : '';
					el.textContent = 'Prev: ' + prev + prevDate + ' | Curr: ' + (row.current_reading || 'N/A') + ' (cons: ' + (row.consumption || '0') + ') on ' + (row.reading_date || '');
				}
			} catch (err) {
				console.error('SSE parse error', err);
			}
		};
		es.onerror = function(err) {
			console.error('SSE error', err);
			es.close();
		};
	} catch (err) {
		console.error('SSE init failed', err);
	}
}

// Initialize a Chart.js line chart for a customer's readings history
function initCustomerChart(customerId) {
	const ctx = document.getElementById('readingsChart');
	if (!ctx) return;

	async function loadAndRender() {
		try {
			const res = await fetch('get_readings_history.php?customer_id=' + encodeURIComponent(customerId));
			if (!res.ok) throw new Error('Network response not ok');
			const rows = await res.json();
			const labels = rows.map(r => r.reading_date);
			const data = rows.map(r => Number(r.current_reading));

			// create or update chart
			if (window._customerChart) {
				window._customerChart.data.labels = labels;
				window._customerChart.data.datasets[0].data = data;
				window._customerChart.update();
			} else {
				window._customerChart = new Chart(ctx, {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: 'Meter Reading',
							data: data,
							borderColor: 'rgba(54, 162, 235, 1)',
							backgroundColor: 'rgba(54, 162, 235, 0.2)',
							fill: true
						}]
					},
					options: {
						responsive: true,
						scales: {
							x: { display: true, title: { display: true, text: 'Date' } },
							y: { display: true, title: { display: true, text: 'Reading' } }
						}
					}
				});
			}
		} catch (err) {
			console.error('Failed to load chart data', err);
		}
	}

	loadAndRender();
	// refresh chart data every 15s to reflect new readings
	window._customerChartInterval = setInterval(loadAndRender, 15000);
}
