/**
 * Prayer Times UI JavaScript
 */

jQuery(document).ready(function($) {
    let currentDate = new Date();
    let selectedCity = 'Dhaka';
    let selectedCountry = 'Bangladesh';
    
    // Initialize
    init();
    
    function init() {
        loadPrayerTimes();
        loadMonthPrayerTimes();
        setupEventHandlers();
    }
    
    function setupEventHandlers() {
        // Tab switching
        $('.tab-btn').on('click', function() {
            const tabName = $(this).data('tab');
            switchTab(tabName);
        });
        
        // Navigation buttons
        $('#prevDay').on('click', previousDay);
        $('#nextDay').on('click', nextDay);
        
        // Location modal
        $('.btn-select-location').on('click', openLocationModal);
        $('#btnClose, .modal-close').on('click', closeLocationModal);
        $('#btnClear').on('click', clearCity);
        $('#btnSelect').on('click', selectCity);
        
        // City search
        $('#citySearch').on('input', searchCities);
        
        // Close modal on background click
        $('#locationModal').on('click', function(e) {
            if (e.target === this) {
                closeLocationModal();
            }
        });
    }
    
    function switchTab(tabName) {
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        $('.tab-btn[data-tab="' + tabName + '"]').addClass('active');
        $('#tab-' + tabName).addClass('active');
        
        if (tabName === 'month') {
            loadMonthPrayerTimes();
        }
    }
    
    function previousDay() {
        currentDate.setDate(currentDate.getDate() - 1);
        updateDateDisplay();
        loadPrayerTimes();
    }
    
    function nextDay() {
        currentDate.setDate(currentDate.getDate() + 1);
        updateDateDisplay();
        loadPrayerTimes();
    }
    
    function updateDateDisplay() {
        const options = { weekday: 'short', month: 'short', day: 'numeric' };
        const dateStr = currentDate.toLocaleDateString('en-US', options);
        $('#currentDate').text(dateStr);
    }
    
    function updateLocationDisplay() {
        const locationBtn = $('.btn-select-location');
        if (selectedCity && selectedCountry) {
            locationBtn.text('📍 ' + selectedCity + ', ' + selectedCountry);
        }
    }
    
    function openLocationModal() {
        $('#locationModal').addClass('show');
        $('#citySearch').focus();
    }
    
    function closeLocationModal() {
        $('#locationModal').removeClass('show');
        $('#citySuggestions').empty();
    }
    
    function clearCity() {
        $('#citySearch').val('');
        $('#citySuggestions').empty();
        $('#selectedCity').val('');
        $('#selectedCountry').val('');
    }
    
    function selectCity() {
        const city = $('#selectedCity').val();
        const country = $('#selectedCountry').val();
        
        if (!city || !country) {
            alert('Please select a city');
            return;
        }
        
        selectedCity = city;
        selectedCountry = country;
        currentDate = new Date();
        
        // Update location display
        updateLocationDisplay();
        updateDateDisplay();
        
        // Reload prayer times
        loadPrayerTimes();
        loadMonthPrayerTimes();
        closeLocationModal();
    }
    
    function searchCities() {
        const searchTerm = $(this).val();
        
        if (searchTerm.length < 2) {
            $('#citySuggestions').empty();
            return;
        }
        
        $.ajax({
            url: prayerTimesAjax.ajaxurl,
            type: 'GET',
            data: {
                action: 'ui_get_cities',
                search: searchTerm,
                nonce: prayerTimesAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderCitySuggestions(response.data);
                } else {
                    $('#citySuggestions').html('<div style="padding: 10px; color: #999;">No cities found</div>');
                }
            },
            error: function() {
                $('#citySuggestions').html('<div style="padding: 10px; color: #f44336;">Error loading cities</div>');
            }
        });
    }
    
    function renderCitySuggestions(cities) {
        let html = '';
        cities.forEach(function(city) {
            html += '<div class="suggestion-item" data-city="' + escapeHtml(city.name) + '" data-country="' + escapeHtml(city.country) + '">';
            html += escapeHtml(city.name) + ', ' + escapeHtml(city.country);
            html += '</div>';
        });
        $('#citySuggestions').html(html);
        
        $('.suggestion-item').on('click', function() {
            const city = $(this).data('city');
            const country = $(this).data('country');
            $('#citySearch').val(city);
            $('#selectedCity').val(city);
            $('#selectedCountry').val(country);
            $('#citySuggestions').empty();
        });
    }
    
    function loadPrayerTimes() {
        const dateStr = currentDate.toISOString().split('T')[0];
        
        $('#todayPrayerTimes').html('<p style="text-align:center;">Loading prayer times for ' + selectedCity + '...</p>');
        
        $.ajax({
            url: prayerTimesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ui_get_prayer_times',
                city: selectedCity,
                country: selectedCountry,
                date: dateStr,
                nonce: prayerTimesAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderPrayerTimes(response.data);
                } else {
                    $('#todayPrayerTimes').html('<p>Failed to load prayer times</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#todayPrayerTimes').html('<p>Error loading prayer times: ' + error + '</p>');
            }
        });
    }
    
    function loadMonthPrayerTimes() {
        $('#monthPrayerTimes').html('<p style="text-align:center;">Loading month prayer times...</p>');
        
        $.ajax({
            url: prayerTimesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ui_get_month_prayer_times',
                city: selectedCity,
                country: selectedCountry,
                year: currentDate.getFullYear(),
                month: currentDate.getMonth() + 1,
                nonce: prayerTimesAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderMonthPrayerTimes(response.data);
                } else {
                    $('#monthPrayerTimes').html('<p>Failed to load month prayer times</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#monthPrayerTimes').html('<p>Error loading month prayer times: ' + error + '</p>');
            }
        });
    }
    
    function renderPrayerTimes(data) {
        let html = '<div class="prayer-cards">';
        
        data.prayers.forEach(function(prayer) {
            html += '<div class="prayer-card">';
            html += '<div class="prayer-icon">' + prayer.icon + '</div>';
            html += '<div class="prayer-name">' + escapeHtml(prayer.name) + '</div>';
            html += '<div class="prayer-time">' + prayer.time + '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        $('#todayPrayerTimes').html(html);
    }
    
    function renderMonthPrayerTimes(data) {
        if (!data || data.length === 0) {
            $('#monthPrayerTimes').html('<p>No prayer times available</p>');
            return;
        }
        
        let html = '<table class="month-prayer-table"><thead><tr>';
        html += '<th>Date</th><th>Hijri</th><th>Fajr</th><th>Sunrise</th><th>Dhuhr</th><th>Asr</th><th>Maghrib</th><th>Isha</th>';
        html += '</tr></thead><tbody>';
        
        data.forEach(function(day) {
            const todayClass = day.is_today ? ' today-row' : '';
            const fajr = day.prayers.Fajr || '----';
            const sunrise = day.prayers.Sunrise || '----';
            const dhuhr = day.prayers.Dhuhr || '----';
            const asr = day.prayers.Asr || '----';
            const maghrib = day.prayers.Maghrib || '----';
            const isha = day.prayers.Isha || '----';
            
            html += '<tr class="' + todayClass + '">';
            html += '<td>' + escapeHtml(day.day_name) + ' ' + day.day + '</td>';
            html += '<td>' + day.hijri_day + ' ' + escapeHtml(day.hijri_month) + '</td>';
            html += '<td>' + fajr + '</td>';
            html += '<td>' + sunrise + '</td>';
            html += '<td>' + dhuhr + '</td>';
            html += '<td>' + asr + '</td>';
            html += '<td>' + maghrib + '</td>';
            html += '<td>' + isha + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        $('#monthPrayerTimes').html(html);
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
