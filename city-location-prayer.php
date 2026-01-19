<?php

/**
 * City Location Prayer Times Shortcode
 * Displays Islamic prayer times with location selection
 */

class CityLocationPrayer
{
    public function __construct()
    {
        add_shortcode('city_location_prayer', [$this, 'display_city_location_prayer']);
    }

    /**
     * Display the prayer times widget
     */
    public function display_city_location_prayer($atts = [])
    {
        ob_start();
?>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            /* body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1e3a8a 100%);
                min-height: 100vh;
                padding: 20px;
            } */

            .container {
                max-width: 1400px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                overflow: hidden;
            }

            .header {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                padding: 30px;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                color: white;
            }

            .tabs {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
            }

            .tab {
                padding: 10px 24px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                transition: all 0.3s;
            }

            .tab.active {
                background: white;
                color: #2563eb;
            }

            .tab:not(.active) {
                background: #1e40af;
                color: white;
            }

            .tab:not(.active):hover {
                background: #1e3a8a;
            }

            .next-prayer {
                color: white;
                font-size: 18px;
            }

            .location-box {
                text-align: right;
            }

            .location-btn {
                display: flex;
                align-items: center;
                gap: 10px;
                background: white;
                color: #2563eb;
                padding: 10px 20px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 10px;
                margin-left: auto;
            }

            .location-btn:hover {
                background: #f0f9ff;
            }

            .date-info {
                font-size: 14px;
                line-height: 1.6;
            }

            .content {
                padding: 40px;
            }

            .prayer-slider {
                position: relative;
                padding: 0 60px;
            }

            .slider-btn {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: white;
                border: none;
                border-radius: 50%;
                width: 44px;
                height: 44px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
                transition: all 0.3s;
            }

            .slider-btn:hover:not(:disabled) {
                background: #f3f4f6;
                transform: translateY(-50%) scale(1.1);
            }

            .slider-btn:disabled {
                opacity: 0.3;
                cursor: not-allowed;
            }

            .slider-btn.prev {
                left: 0;
            }

            .slider-btn.next {
                right: 0;
            }

            .prayer-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 20px;
            }

            .prayer-card {
                padding: 24px;
                border-radius: 12px;
                background: #f9fafb;
                transition: all 0.3s;
            }

            .prayer-card:hover {
                background: #f3f4f6;
            }

            .prayer-card.active {
                background: #3b82f6;
                color: white;
                transform: scale(1.05);
                box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
            }

            .prayer-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
            }

            .prayer-icon {
                font-size: 28px;
            }

            .prayer-name {
                font-size: 18px;
                font-weight: 600;
            }

            .prayer-time {
                font-size: 28px;
                font-weight: 700;
            }

            .modal {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 1000;
                padding: 20px;
            }

            .modal.show {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            html:has(.modal.show) {
                overflow: hidden;
                
            }
            html:has(.modal.show)::after{
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .modal-content {
                background: white;
                padding: 30px;
                border-radius: 12px;
                width: 100%;
                max-width: 500px;
                max-height: 90vh;
                overflow-y: auto;
            }

            .modal-header {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .modal-subtitle {
                color: #6b7280;
                margin-bottom: 20px;
            }

            .search-input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 16px;
                margin-bottom: 15px;
            }

            .search-input:focus {
                outline: none;
                border-color: #3b82f6;
            }

            .city-list {
                max-height: 300px;
                overflow-y: auto;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            .city-item {
                padding: 15px;
                border-bottom: 1px solid #e5e7eb;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 10px;
                transition: background 0.2s;
            }

            .city-item:last-child {
                border-bottom: none;
            }

            .city-item:hover {
                background: #f3f4f6;
            }

            .modal-buttons {
                display: flex;
                gap: 15px;
            }

            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }

            .btn-clear {
                background: #ef4444;
                color: white;
            }

            .btn-clear:hover {
                background: #dc2626;
            }

            .btn-close {
                flex: 1;
                background: #3b82f6;
                color: white;
            }

            .btn-close:hover {
                background: #2563eb;
            }

            .month-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }

            .month-table th,
            .month-table td {
                padding: 12px;
                border: 1px solid #e5e7eb;
                text-align: center;
            }

            .month-table th {
                background: #f3f4f6;
                font-weight: 600;
            }

            .month-table tr:hover {
                background: #f9fafb;
            }

            .month-table tr.today {
                background: #3b82f6;
                color: white;
            }

            .loading {
                text-align: center;
                padding: 60px;
                color: #6b7280;
            }

            .spinner {
                border: 3px solid #e5e7eb;
                border-top: 3px solid #3b82f6;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .placeholder {
                text-align: center;
                padding: 80px 20px;
                color: #9ca3af;
            }

            .placeholder-text {
                font-size: 20px;
            }
        </style>

        <div class="container">
            <div class="header">
                <div>
                    <div class="tabs">
                        <button class="tab active" onclick="switchTab('today')">Today</button>
                        <button class="tab" onclick="switchTab('month')">Month</button>
                    </div>
                    <div class="prayer" id="citynextPrayerText">Next prayer in 00:00:00</div>
                </div>
                <div class="location-box">
                    <button class="location-btn" onclick="openLocationModal()">
                        <span id="locationText">Select Location</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <div class="date-info" id="dateInfo"></div>
                </div>
            </div>

            <div class="content">
                <div id="todayView" class="view">
                    <div id="todayContent"></div>
                </div>
                <div id="monthView" class="view" style="display: none;">
                    <div id="monthContent"></div>
                </div>
            </div>
        </div>

        <div class="modal" id="locationModal">
            <div class="modal-content">
                <div class="modal-header">Enter Location</div>
                <div class="modal-subtitle">Specify a location to display prayer times</div>
                <input type="text" class="search-input" id="searchInput" placeholder="Search city..." oninput="filterCities()">
                <div class="city-list" id="cityList"></div>
                <div class="modal-buttons">
                    <button class="btn btn-clear" onclick="clearSearch()">Clear</button>
                    <button class="btn btn-close" onclick="closeLocationModal()">Close</button>
                </div>
            </div>
        </div>

        <script>
            const countries = {
                "countries": [{
                        "code": "AF",
                        "name": "Afghanistan",
                        "timezone": "Asia/Kabul",
                        "cities": ["Kabul", "Herat", "Kandahar", "Jalalabad", "Mazar-i-Sharif", "Kunduz", "Khost", "Puli Khumri", "Takhar", "Ghazni"]
                    },
                    {
                        "code": "AL",
                        "name": "Albania",
                        "timezone": "Europe/Tirane",
                        "cities": ["Tirane", "Durres", "Vlore", "Shkoder", "Korce"]
                    },
                    {
                        "code": "DZ",
                        "name": "Algeria",
                        "timezone": "Africa/Algiers",
                        "cities": ["Algiers", "Oran", "Constantine", "Annaba", "Blida", "Setif", "Sidi Bel Abbes", "Batna", "Skikda"]
                    },
                    {
                        "code": "AS",
                        "name": "American Samoa",
                        "timezone": "Pacific/Pago_Pago",
                        "cities": ["Pago Pago"]
                    },
                    {
                        "code": "AD",
                        "name": "Andorra",
                        "timezone": "Europe/Andorra",
                        "cities": ["Andorra la Vella"]
                    },
                    {
                        "code": "AO",
                        "name": "Angola",
                        "timezone": "Africa/Luanda",
                        "cities": ["Luanda", "Huambo", "Benguela", "Kuito"]
                    },
                    {
                        "code": "AG",
                        "name": "Antigua and Barbuda",
                        "timezone": "America/Antigua",
                        "cities": ["St. Johns"]
                    },
                    {
                        "code": "BB",
                        "name": "Barbados",
                        "timezone": "America/Barbados",
                        "cities": ["Bridgetown"]
                    },
                    {
                        "code": "BS",
                        "name": "Bahamas",
                        "timezone": "America/Nassau",
                        "cities": ["Nassau"]
                    },
                    {
                        "code": "BH",
                        "name": "Bahrain",
                        "timezone": "Asia/Bahrain",
                        "cities": ["Manama", "Muharraq", "Riffa", "Isa Town"]
                    },
                    {
                        "code": "BD",
                        "name": "Bangladesh",
                        "timezone": "Asia/Dhaka",
                        "cities": ["Dhaka", "Chittagong", "Khulna", "Rajshahi", "Sylhet", "Barisal", "Rangpur", "Mymensingh"]
                    },
                    {
                        "code": "BZ",
                        "name": "Belize",
                        "timezone": "America/Belize",
                        "cities": ["Belmopan", "Belize City"]
                    },
                    {
                        "code": "BY",
                        "name": "Belarus",
                        "timezone": "Europe/Minsk",
                        "cities": ["Minsk", "Brest", "Grodno", "Vitebsk"]
                    },
                    {
                        "code": "BE",
                        "name": "Belgium",
                        "timezone": "Europe/Brussels",
                        "cities": ["Brussels", "Antwerp", "Ghent", "Charleroi"]
                    },
                    {
                        "code": "KH",
                        "name": "Cambodia",
                        "timezone": "Asia/Phnom_Penh",
                        "cities": ["Phnom Penh", "Siem Reap", "Battambang", "Kampong Cham"]
                    },
                    {
                        "code": "CM",
                        "name": "Cameroon",
                        "timezone": "Africa/Douala",
                        "cities": ["Douala", "Yaounde", "Bamenda"]
                    },
                    {
                        "code": "CA",
                        "name": "Canada",
                        "timezone": "America/Toronto",
                        "cities": ["Toronto", "Vancouver", "Montreal", "Calgary", "Ottawa", "Edmonton", "Winnipeg"]
                    },
                    {
                        "code": "CV",
                        "name": "Cape Verde",
                        "timezone": "Atlantic/Cape_Verde",
                        "cities": ["Praia"]
                    },
                    {
                        "code": "KY",
                        "name": "Cayman Islands",
                        "timezone": "America/Cayman",
                        "cities": ["George Town"]
                    },
                    {
                        "code": "CF",
                        "name": "Central African Republic",
                        "timezone": "Africa/Bangui",
                        "cities": ["Bangui"]
                    },
                    {
                        "code": "EG",
                        "name": "Egypt",
                        "timezone": "Africa/Cairo",
                        "cities": ["Cairo", "Alexandria", "Giza", "Aswan", "Luxor", "Port Said", "Suez"]
                    },
                    {
                        "code": "SA",
                        "name": "Saudi Arabia",
                        "timezone": "Asia/Riyadh",
                        "cities": ["Riyadh", "Jeddah", "Mecca", "Medina", "Dammam", "Abha"]
                    },
                    {
                        "code": "AE",
                        "name": "United Arab Emirates",
                        "timezone": "Asia/Dubai",
                        "cities": ["Dubai", "Abu Dhabi", "Sharjah", "Ajman", "Ras Al Khaimah"]
                    },
                    {
                        "code": "TR",
                        "name": "Turkey",
                        "timezone": "Europe/Istanbul",
                        "cities": ["Istanbul", "Ankara", "Izmir", "Bursa", "Antalya"]
                    },
                    {
                        "code": "PK",
                        "name": "Pakistan",
                        "timezone": "Asia/Karachi",
                        "cities": ["Karachi", "Lahore", "Islamabad", "Rawalpindi", "Multan", "Peshawar"]
                    },
                    {
                        "code": "MY",
                        "name": "Malaysia",
                        "timezone": "Asia/Kuala_Lumpur",
                        "cities": ["Kuala Lumpur", "Penang", "Johor Bahru", "Ipoh", "Selangor"]
                    },
                    {
                        "code": "ID",
                        "name": "Indonesia",
                        "timezone": "Asia/Jakarta",
                        "cities": ["Jakarta", "Surabaya", "Bandung", "Medan", "Semarang", "Makassar"]
                    },
                    {
                        "code": "GB",
                        "name": "United Kingdom",
                        "timezone": "Europe/London",
                        "cities": ["London", "Manchester", "Birmingham", "Leeds", "Glasgow"]
                    },
                    {
                        "code": "US",
                        "name": "United States",
                        "timezone": "America/New_York",
                        "cities": ["New York", "Los Angeles", "Chicago", "Houston", "Phoenix", "Philadelphia"]
                    },
                    {
                        "code": "DE",
                        "name": "Germany",
                        "timezone": "Europe/Berlin",
                        "cities": ["Berlin", "Munich", "Hamburg", "Cologne", "Frankfurt"]
                    },
                    {
                        "code": "FR",
                        "name": "France",
                        "timezone": "Europe/Paris",
                        "cities": ["Paris", "Lyon", "Marseille", "Toulouse", "Nice"]
                    },
                    {
                        "code": "JO",
                        "name": "Jordan",
                        "timezone": "Asia/Amman",
                        "cities": ["Amman", "Zarqa", "Irbid", "Aqaba"]
                    },
                    {
                        "code": "LB",
                        "name": "Lebanon",
                        "timezone": "Asia/Beirut",
                        "cities": ["Beirut", "Tripoli", "Sidon", "Tyre"]
                    },
                    {
                        "code": "PS",
                        "name": "Palestine",
                        "timezone": "Asia/Hebron",
                        "cities": ["Gaza City", "Ramallah", "Bethlehem"]
                    },
                    {
                        "code": "IQ",
                        "name": "Iraq",
                        "timezone": "Asia/Baghdad",
                        "cities": ["Baghdad", "Basra", "Mosul", "Kirkuk"]
                    }
                ]
            };

            const prayerNames = [{
                    name: 'Fajr',
                    icon: '🌅',
                    key: 'Fajr'
                },
                {
                    name: 'Sunrise',
                    icon: '☀️',
                    key: 'Sunrise'
                },
                {
                    name: 'Dhuhr',
                    icon: '☀️',
                    key: 'Dhuhr'
                },
                {
                    name: 'Asr',
                    icon: '🌤️',
                    key: 'Asr'
                },
                {
                    name: 'Maghrib',
                    icon: '🌆',
                    key: 'Maghrib'
                },
                {
                    name: 'Isha',
                    icon: '🌙',
                    key: 'Isha'
                }
            ];

            let selectedLocation = null;
            let prayerTimes = null;
            let currentDayIndex = 0;
            let monthData = [];
            let currentTab = 'today';

            function openLocationModal() {
                document.getElementById('locationModal').classList.add('show');
                document.getElementById('searchInput').value = '';
                document.getElementById('cityList').innerHTML = '';
            }

            function closeLocationModal() {
                document.getElementById('locationModal').classList.remove('show');
            }

            function clearSearch() {
                document.getElementById('searchInput').value = '';
                document.getElementById('cityList').innerHTML = '';
            }

            function filterCities() {
                const query = document.getElementById('searchInput').value.toLowerCase();
                const cityList = document.getElementById('cityList');

                if (!query) {
                    cityList.innerHTML = '';
                    return;
                }

                const filtered = [];
                countries.countries.forEach(function(country) {
                    country.cities.forEach(function(city) {
                        if (city.toLowerCase().includes(query) || country.name.toLowerCase().includes(query)) {
                            filtered.push({
                                city: city,
                                country: country.name
                            });
                        }
                    });
                });

                cityList.innerHTML = filtered.slice(0, 10).map(function(item) {
                    return '<div class="city-item" onclick="selectLocation(\'' + item.city + '\', \'' + item.country + '\')">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                        '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>' +
                        '<circle cx="12" cy="10" r="3"></circle>' +
                        '</svg>' +
                        '<span>' + item.city + ', ' + item.country + '</span>' +
                        '</div>';
                }).join('');
            }

            async function selectLocation(city, country) {
                selectedLocation = {
                    city: city,
                    country: country
                };
                document.getElementById('locationText').textContent = city + ', ' + country;
                closeLocationModal();

                currentDayIndex = 0;
                const today = new Date();
                const dateStr = today.getDate() + '-' + (today.getMonth() + 1) + '-' + today.getFullYear();

                await fetchPrayerTimes(dateStr);
                await fetchMonthPrayerTimes(today.getFullYear(), today.getMonth() + 1);

                if (currentTab === 'today') {
                    renderTodayView();
                } else {
                    renderMonthView();
                }
            }

            async function fetchPrayerTimes(dateStr) {
                try {
                    const response = await fetch(
                        'https://api.aladhan.com/v1/timingsByCity/' + dateStr + '?city=' + selectedLocation.city + '&country=' + selectedLocation.country
                    );
                    const data = await response.json();
                    prayerTimes = data.data;
                    updateDateInfo();
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            async function fetchMonthPrayerTimes(year, month) {
                try {
                    const response = await fetch(
                        'https://api.aladhan.com/v1/calendarByCity/' + year + '/' + month + '?city=' + selectedLocation.city + '&country=' + selectedLocation.country
                    );
                    const data = await response.json();
                    monthData = data.data;
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function updateDateInfo() {
                if (!prayerTimes) return;
                const hijri = prayerTimes.date.hijri;
                const gregorian = prayerTimes.date.gregorian;
                document.getElementById('dateInfo').innerHTML =
                    '<div>' + hijri.day + ' ' + hijri.month.en + ', ' + hijri.year + '</div>' +
                    '<div>' + gregorian.day + ' ' + gregorian.month.en + ', ' + gregorian.year + '</div>';
            }

            function getCurrentPrayer() {
                if (!prayerTimes) return null;

                const now = new Date();
                const currentTime = now.getHours() * 60 + now.getMinutes();
                let currentPrayer = 'Fajr';

                prayerNames.forEach(function(prayer) {
                    if (prayer.key === 'Sunrise') return;
                    const time = prayerTimes.timings[prayer.key].split(' ')[0];
                    const parts = time.split(':');
                    const hours = parseInt(parts[0]);
                    const minutes = parseInt(parts[1]);
                    const prayerMinutes = hours * 60 + minutes;

                    if (prayerMinutes <= currentTime) {
                        currentPrayer = prayer.key;
                    }
                });

                return currentPrayer;
            }

            function getNextPrayerTime() {
                if (!prayerTimes) return '00:00:00';

                const now = new Date();
                const currentTime = now.getHours() * 60 + now.getMinutes();

                for (let i = 0; i < prayerNames.length; i++) {
                    const prayer = prayerNames[i];
                    if (prayer.key === 'Sunrise') continue;
                    const time = prayerTimes.timings[prayer.key].split(' ')[0];
                    const parts = time.split(':');
                    const hours = parseInt(parts[0]);
                    const minutes = parseInt(parts[1]);
                    const prayerMinutes = hours * 60 + minutes;

                    if (prayerMinutes > currentTime) {
                        const diff = prayerMinutes - currentTime;
                        const h = Math.floor(diff / 60);
                        const m = diff % 60;
                        const s = 60 - now.getSeconds();
                        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                    }
                }
                return '00:00:00';
            }

            function updateNextPrayerTime() {
                const nextTime = getNextPrayerTime();
                document.getElementById('citynextPrayerText').textContent = 'Next prayer in ' + nextTime;
            }

            function renderTodayView() {
                const content = document.getElementById('todayContent');

                if (!selectedLocation) {
                    content.innerHTML = '<div class="placeholder"><div class="placeholder-text">Please select a location to view prayer times</div></div>';
                    return;
                }

                if (!prayerTimes) {
                    content.innerHTML = '<div class="loading"><div class="spinner"></div><div>Loading...</div></div>';
                    return;
                }

                const currentPrayer = getCurrentPrayer();

                let cardsHTML = '';
                prayerNames.forEach(function(prayer) {
                    const isActive = currentPrayer === prayer.key ? 'active' : '';
                    const time = prayerTimes.timings[prayer.key].split(' ')[0];
                    cardsHTML += '<div class="prayer-card ' + isActive + '">' +
                        '<div class="prayer-header">' +
                        '<span class="prayer-icon">' + prayer.icon + '</span>' +
                        '<span class="prayer-name">' + prayer.name + '</span>' +
                        '</div>' +
                        '<div class="prayer-time">' + time + '</div>' +
                        '</div>';
                });

                const prevDisabled = currentDayIndex === 0 ? 'disabled' : '';

                content.innerHTML = '<div class="prayer-slider">' +
                    '<button class="slider-btn prev" onclick="previousDay()" ' + prevDisabled + '>' +
                    '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                    '<polyline points="15 18 9 12 15 6"></polyline>' +
                    '</svg>' +
                    '</button>' +
                    '<div class="prayer-grid">' + cardsHTML + '</div>' +
                    '<button class="slider-btn next" onclick="nextDay()">' +
                    '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                    '<polyline points="9 18 15 12 9 6"></polyline>' +
                    '</svg>' +
                    '</button>' +
                    '</div>';
            }

            function renderTodayPlaceholder() {
                const content = document.getElementById('todayContent');

                let cardsHTML = '';
                prayerNames.forEach(function(prayer) {
                    cardsHTML += '<div class="prayer-card">' +
                        '<div class="prayer-header">' +
                        '<span class="prayer-icon">' + prayer.icon + '</span>' +
                        '<span class="prayer-name">' + prayer.name + '</span>' +
                        '</div>' +
                        '<div class="prayer-time">-----</div>' +
                        '</div>';
                });

                content.innerHTML = '<div class="prayer-slider">' +
                    '<button class="slider-btn prev" disabled>' +
                    '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                    '<polyline points="15 18 9 12 15 6"></polyline>' +
                    '</svg>' +
                    '</button>' +
                    '<div class="prayer-grid">' + cardsHTML + '</div>' +
                    '<button class="slider-btn next" disabled>' +
                    '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                    '<polyline points="9 18 15 12 9 6"></polyline>' +
                    '</svg>' +
                    '</button>' +
                    '</div>';
            }

            function renderMonthView() {
                const content = document.getElementById('monthContent');

                if (!selectedLocation) {
                    content.innerHTML = '<div class="placeholder"><div class="placeholder-text">Please select a location to view monthly prayer times</div></div>';
                    return;
                }

                if (monthData.length === 0) {
                    content.innerHTML = '<div class="loading"><div class="spinner"></div><div>Loading...</div></div>';
                    return;
                }

                const today = new Date();
                const todayStr = today.toISOString().split('T')[0];

                let rowsHTML = '';
                monthData.forEach(function(day) {
                    const gregorianDate = day.date.gregorian.date;
                    const isToday = gregorianDate === todayStr ? 'today' : '';
                    const dayNum = day.date.gregorian.day.padStart(2, '0');
                    const weekday = day.date.gregorian.weekday.en.substring(0, 3);

                    rowsHTML += '<tr class="' + isToday + '">' +
                        '<td>' + dayNum + ' ' + weekday + '</td>' +
                        '<td>' + day.date.hijri.day + '</td>' +
                        '<td>' + day.timings.Fajr.split(' ')[0] + '</td>' +
                        '<td>' + day.timings.Sunrise.split(' ')[0] + '</td>' +
                        '<td>' + day.timings.Dhuhr.split(' ')[0] + '</td>' +
                        '<td>' + day.timings.Asr.split(' ')[0] + '</td>' +
                        '<td>' + day.timings.Maghrib.split(' ')[0] + '</td>' +
                        '<td>' + day.timings.Isha.split(' ')[0] + '</td>' +
                        '</tr>';
                });

                content.innerHTML = '<table class="month-table">' +
                    '<thead>' +
                    '<tr>' +
                    '<th>Date</th>' +
                    '<th>Hijri</th>' +
                    '<th>Fajr</th>' +
                    '<th>Sunrise</th>' +
                    '<th>Dhuhr</th>' +
                    '<th>Asr</th>' +
                    '<th>Maghrib</th>' +
                    '<th>Isha</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>' + rowsHTML + '</tbody>' +
                    '</table>';
            }

            async function nextDay() {
                if (!selectedLocation) return;

                currentDayIndex++;
                const today = new Date();
                today.setDate(today.getDate() + currentDayIndex);
                const dateStr = today.getDate() + '-' + (today.getMonth() + 1) + '-' + today.getFullYear();

                await fetchPrayerTimes(dateStr);
                renderTodayView();
            }

            async function previousDay() {
                if (!selectedLocation || currentDayIndex === 0) return;

                currentDayIndex--;
                const today = new Date();
                today.setDate(today.getDate() + currentDayIndex);
                const dateStr = today.getDate() + '-' + (today.getMonth() + 1) + '-' + today.getFullYear();

                await fetchPrayerTimes(dateStr);
                renderTodayView();
            }

            function switchTab(tab) {
                currentTab = tab;
                const tabs = document.querySelectorAll('.tab');
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                event.target.classList.add('active');

                if (tab === 'today') {
                    document.getElementById('todayView').style.display = 'block';
                    document.getElementById('monthView').style.display = 'none';
                    if (selectedLocation) {
                        renderTodayView();
                    } else {
                        renderTodayPlaceholder();
                    }
                } else {
                    document.getElementById('todayView').style.display = 'none';
                    document.getElementById('monthView').style.display = 'block';
                    renderMonthView();
                }
            }

            // Initialize
            renderTodayPlaceholder();
            setInterval(updateNextPrayerTime, 1000);
        </script>
<?php
        return ob_get_clean();
    }
}

// Initialize the shortcode
new CityLocationPrayer();
