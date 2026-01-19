jQuery(document).ready(function($) {
    if (typeof athanData !== 'undefined') {
        updateNextPrayerCountdown();
        setInterval(updateNextPrayerCountdown, 1000);
    }
    
    // ============ Countries List Functionality ============
    if (typeof athanCountriesData !== 'undefined') {
        initCountriesFeature();
    }
    
    // ============ Country Detail Functionality ============
    if (typeof athanCountryDetail !== 'undefined') {
        initCountryDetailFeature();
    }
    
    // ============ City Detail Functionality ============
    if (typeof athanCityDetail !== 'undefined') {
        initCityDetailFeature();
    }
    
    // ============ City Link Click Handler ============
    $(document).on('click', '.athan-city-link', function(e) {
        e.preventDefault();
        const city = $(this).data('city');
        const country = $(this).data('country');
        const countryCode = $(this).data('country-code');
        
        // Navigate to city detail page
        const baseUrl = (typeof athanData !== 'undefined' && athanData.cityDetailUrl) ? athanData.cityDetailUrl : '/city-detail/';
        const url = `${baseUrl}?city=${encodeURIComponent(city)}&country=${encodeURIComponent(country)}&country_code=${countryCode}`;
        window.location.href = url;
    });
    
    // ============ Organized City Link Click Handler ============
    $(document).on('click', '.athan-city-link-organized', function(e) {
        e.preventDefault();
        const city = $(this).data('city');
        const country = $(this).data('country');
        const countryCode = $(this).data('country-code');
        
        // Navigate to city detail page
        const baseUrl = (typeof athanData !== 'undefined' && athanData.cityDetailUrl) ? athanData.cityDetailUrl : '/city-detail/';
        const url = `${baseUrl}?city=${encodeURIComponent(city)}&country=${encodeURIComponent(country)}&country_code=${countryCode}`;
        window.location.href = url;
    });
    
    function updateNextPrayerCountdown() {
        const times = athanData.times;
        const now = new Date();
        const currentTime = now.toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        const prayerOrder = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        let nextPrayer = null;
        let nextPrayerTime = null;
        
        // Find next prayer
        for (const prayer of prayerOrder) {
            const prayerTime = times[prayer];
            if (compareTimes(currentTime, prayerTime) < 0) {
                nextPrayer = prayer;
                nextPrayerTime = prayerTime;
                break;
            }
        }
        
        // If no more prayers today, show first prayer of next day
        if (!nextPrayer) {
            nextPrayer = 'Fajr';
            nextPrayerTime = times.Fajr;
            // Add 24 hours for next day
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            now.setHours(0, 0, 0, 0);
        }
        
        // Calculate time difference
        const [nextHour, nextMinute] = nextPrayerTime.split(':');
        const nextDate = new Date(now);
        nextDate.setHours(parseInt(nextHour), parseInt(nextMinute), 0, 0);
        
        const diffMs = nextDate - now;
        const diffSec = Math.floor(diffMs / 1000);
        
        if (diffSec < 0) {
            // Prayer time passed, recalculate
            return;
        }
        
        const hours = Math.floor(diffSec / 3600);
        const minutes = Math.floor((diffSec % 3600) / 60);
        const seconds = diffSec % 60;
        
        // Update display
        const countdownText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        $('#athan-countdown').text(countdownText);
        
        // Update next prayer text
        $('.next-prayer').html(`Next prayer (<strong>${nextPrayer}</strong>) in <span id="athan-countdown">${countdownText}</span>`);
    }
    
    function compareTimes(time1, time2) {
        const [h1, m1] = time1.split(':').map(Number);
        const [h2, m2] = time2.split(':').map(Number);
        
        if (h1 !== h2) return h1 - h2;
        return m1 - m2;
    }
    
    // Tab switching functionality
    $('.athan-tab').on('click', function(e) {
        e.preventDefault();
        const tab = $(this).data('tab');
        
        $('.athan-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.athan-tab-content').removeClass('active');
        $(`#athan-${tab}`).addClass('active');
    });
    
    // ============ Countries Feature Functions ============
    
    function initCountriesFeature() {
        // Get country emoji flags
        const countryFlags = {
            'AF': '🇦🇫', 'AL': '🇦🇱', 'DZ': '🇩🇿', 'AS': '🇦🇸', 'AD': '🇦🇩', 'AO': '🇦🇴', 'AG': '🇦🇬', 'AR': '🇦🇷', 'AM': '🇦🇲', 'AW': '🇦🇼', 'AU': '🇦🇺', 'AT': '🇦🇹', 'AZ': '🇦🇿', 'BS': '🇧🇸', 'BH': '🇧🇭', 'BD': '🇧🇩', 'BB': '🇧🇧', 'BY': '🇧🇾', 'BE': '🇧🇪', 'BZ': '🇧🇿', 'BJ': '🇧🇯', 'BM': '🇧🇲', 'BT': '🇧🇹', 'BO': '🇧🇴', 'BA': '🇧🇦', 'BW': '🇧🇼', 'BR': '🇧🇷', 'IO': '🇮🇴', 'BN': '🇧🇳', 'BG': '🇧🇬', 'BF': '🇧🇫', 'BI': '🇧🇮', 'KH': '🇰🇭', 'CM': '🇨🇲', 'CA': '🇨🇦', 'CV': '🇨🇻', 'BQ': '🇧🇶', 'KY': '🇰🇾', 'CF': '🇨🇫', 'TD': '🇹🇩', 'CL': '🇨🇱', 'CN': '🇨🇳', 'CX': '🇨🇽', 'CO': '🇨🇴', 'KM': '🇰🇲', 'CK': '🇨🇰', 'CR': '🇨🇷', 'HR': '🇭🇷', 'CU': '🇨🇺', 'CW': '🇨🇼', 'CY': '🇨🇾', 'CZ': '🇨🇿', 'DK': '🇩🇰', 'DJ': '🇩🇯', 'DM': '🇩🇲', 'DO': '🇩🇴', 'CD': '🇨🇩', 'EC': '🇪🇨', 'EG': '🇪🇬', 'SV': '🇸🇻', 'GQ': '🇬🇶', 'ER': '🇪🇷', 'EE': '🇪🇪', 'SZ': '🇸🇿', 'ET': '🇪🇹', 'FK': '🇫🇰', 'FO': '🇫🇴', 'FJ': '🇫🇯', 'FI': '🇫🇮', 'FR': '🇫🇷', 'GF': '🇬🇫', 'PF': '🇵🇫', 'GA': '🇬🇦', 'GM': '🇬🇲', 'GE': '🇬🇪', 'DE': '🇩🇪', 'GH': '🇬🇭', 'GI': '🇬🇮', 'GR': '🇬🇷', 'GL': '🇬🇱', 'GD': '🇬🇩', 'GP': '🇬🇵', 'GU': '🇬🇺', 'GT': '🇬🇹', 'GN': '🇬🇳', 'GW': '🇬🇼', 'GY': '🇬🇾', 'HT': '🇭🇹', 'HN': '🇭🇳', 'HU': '🇭🇺', 'IS': '🇮🇸', 'IN': '🇮🇳', 'ID': '🇮🇩', 'IR': '🇮🇷', 'IQ': '🇮🇶', 'IE': '🇮🇪', 'IL': '🇮🇱', 'IT': '🇮🇹', 'JM': '🇯🇲', 'JP': '🇯🇵', 'JO': '🇯🇴', 'KZ': '🇰🇿', 'KE': '🇰🇪', 'KI': '🇰🇮', 'KP': '🇰🇵', 'KR': '🇰🇷', 'XK': '🇽🇰', 'KW': '🇰🇼', 'KG': '🇰🇬', 'LA': '🇱🇦', 'LV': '🇱🇻', 'LB': '🇱🇧', 'LS': '🇱🇸', 'LR': '🇱🇷', 'LY': '🇱🇾', 'LI': '🇱🇮', 'LT': '🇱🇹', 'LU': '🇱🇺', 'MO': '🇲🇴', 'MG': '🇲🇬', 'MW': '🇲🇼', 'MY': '🇲🇾', 'MV': '🇲🇻', 'ML': '🇲🇱', 'MT': '🇲🇹', 'MH': '🇲🇭', 'MQ': '🇲🇶', 'MR': '🇲🇷', 'MU': '🇲🇺', 'YT': '🇾🇹', 'MX': '🇲🇽', 'FM': '🇫🇲', 'MD': '🇲🇩', 'MC': '🇲🇨', 'MN': '🇲🇳', 'ME': '🇲🇪', 'MS': '🇲🇸', 'MA': '🇲🇦', 'MZ': '🇲🇿', 'MM': '🇲🇲', 'NA': '🇳🇦', 'NR': '🇳🇷', 'NP': '🇳🇵', 'NL': '🇳🇱', 'NC': '🇳🇨', 'NZ': '🇳🇿', 'NI': '🇳🇮', 'NE': '🇳🇪', 'NG': '🇳🇬', 'NU': '🇳🇺', 'NF': '🇳🇫', 'MK': '🇲🇰', 'MP': '🇲🇵', 'NO': '🇳🇴', 'OM': '🇴🇲', 'PK': '🇵🇰', 'PW': '🇵🇼', 'PS': '🇵🇸', 'PA': '🇵🇦', 'PG': '🇵🇬', 'PY': '🇵🇾', 'PE': '🇵🇪', 'PH': '🇵🇭', 'PN': '🇵🇳', 'PL': '🇵🇱', 'PT': '🇵🇹', 'PR': '🇵🇷', 'QA': '🇶🇦', 'RE': '🇷🇪', 'RO': '🇷🇴', 'RU': '🇷🇺', 'RW': '🇷🇼', 'SH': '🇸🇭', 'KN': '🇰🇳', 'LC': '🇱🇨', 'PM': '🇵🇲', 'VC': '🇻🇨', 'WS': '🇼🇸', 'SM': '🇸🇲', 'ST': '🇸🇹', 'SA': '🇸🇦', 'SN': '🇸🇳', 'RS': '🇷🇸', 'SC': '🇸🇨', 'SL': '🇸🇱', 'SG': '🇸🇬', 'SX': '🇸🇽', 'SK': '🇸🇰', 'SI': '🇸🇮', 'SB': '🇸🇧', 'SO': '🇸🇴', 'ZA': '🇿🇦', 'GS': '🇬🇸', 'SS': '🇸🇸', 'ES': '🇪🇸', 'LK': '🇱🇰', 'SD': '🇸🇩', 'SR': '🇸🇷', 'SJ': '🇸🇯', 'SE': '🇸🇪', 'CH': '🇨🇭', 'SY': '🇸🇾', 'TW': '🇹🇼', 'TJ': '🇹🇯', 'TZ': '🇹🇿', 'TH': '🇹🇭', 'TL': '🇹🇱', 'TG': '🇹🇬', 'TK': '🇹🇰', 'TO': '🇹🇴', 'TT': '🇹🇹', 'TN': '🇹🇳', 'TR': '🇹🇷', 'TM': '🇹🇲', 'TC': '🇹🇨', 'TV': '🇹🇻', 'UG': '🇺🇬', 'UA': '🇺🇦', 'AE': '🇦🇪', 'GB': '🇬🇧', 'US': '🇺🇸', 'UY': '🇺🇾', 'UZ': '🇺🇿', 'VU': '🇻🇺', 'VA': '🇻🇦', 'VE': '🇻🇪', 'VN': '🇻🇳', 'WF': '🇼🇫', 'EH': '🇪🇭', 'YE': '🇾🇪', 'ZM': '🇿🇲', 'ZW': '🇿🇼', 'AX': '🇦🇽'
        };
        
        // Set flag emojis
        athanCountriesData.forEach(country => {
            const flag = countryFlags[country.code] || '🌍';
            $(`#flag-${country.code}`).text(flag);
        });
        
        // Fetch and display capital city current times for countries
        athanCountriesData.forEach(country => {
            if (country.capital) {
                fetchCapitalCurrentTime(country.code, country.capital, country.offset);
            }
        });
        
        // Country search functionality
        $('#athan-countries-search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.athan-country-item').each(function() {
                const countryName = $(this).data('country-name').toLowerCase();
                $(this).toggle(countryName.includes(searchTerm));
            });
        });
        
        // Country item click
        $(document).on('click', '.athan-country-item', function(e) {
            e.preventDefault();
            const countryCode = $(this).data('country');
            const countryName = $(this).data('country-name');
            
            // Store in sessionStorage for detail page
            sessionStorage.setItem('selectedCountry', JSON.stringify({
                code: countryCode,
                name: countryName
            }));
            
            // Navigate to country detail page
            navigateToCountryDetail(countryCode, countryName);
        });
    }
    
    function fetchCapitalCurrentTime(countryCode, capital, offset) {
        // Calculate current time in the timezone offset
        const now = new Date();
        
        // Parse offset string like "+04:30" or "-05:00"
        const sign = offset[0] === '+' ? 1 : -1;
        const [offsetHours, offsetMinutes] = offset.slice(1).split(':').map(Number);
        const offsetMs = sign * (offsetHours * 3600 + offsetMinutes * 60) * 1000;
        
        // Get UTC time
        const utcTime = now.getTime() + now.getTimezoneOffset() * 60 * 1000;
        
        // Get local time in target timezone
        const localTime = new Date(utcTime + offsetMs);
        
        // Format time as HH:MM AM/PM
        const timeString = localTime.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        
        $(`#time-${countryCode}`).text(timeString);
        
        // Update every second
        if (!window.capitalTimeIntervals) {
            window.capitalTimeIntervals = {};
        }
        
        window.capitalTimeIntervals[countryCode] = setInterval(function() {
            const now = new Date();
            const utcTime = now.getTime() + now.getTimezoneOffset() * 60 * 1000;
            const localTime = new Date(utcTime + offsetMs);
            const timeString = localTime.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            $(`#time-${countryCode}`).text(timeString);
        }, 1000);
    }
    
    function fetchCountryPrayerTime(country, city, elementId) {
        $.ajax({
            type: 'POST',
            url: athanData.ajaxUrl,
            data: {
                action: 'get_prayer_times',
                nonce: athanData.nonce,
                city: city,
                country: country
            },
            success: function(response) {
                if (response.success) {
                    const times = response.data;
                    const nextPrayer = getNextPrayerTime(times);
                    $(`#${elementId}`).text(nextPrayer.time).attr('title', nextPrayer.name);
                }
            }
        });
    }
    
    function getNextPrayerTime(times) {
        const now = new Date();
        const currentTime = now.toLocaleTimeString('en-US', { 
            hour12: false, hour: '2-digit', minute: '2-digit'
        });
        
        const prayerOrder = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        for (const prayer of prayerOrder) {
            if (compareTimes(currentTime, times[prayer]) < 0) {
                return { name: prayer, time: times[prayer] };
            }
        }
        return { name: 'Fajr', time: times.Fajr };
    }
    
    function navigateToCountryDetail(countryCode, countryName) {
        // Navigate to the countries-detail page with parameters
        const baseUrl = (typeof athanData !== 'undefined' && athanData.countryDetailUrl) ? athanData.countryDetailUrl : '/countries-detail/';
        const detailUrl = `${baseUrl}?country=${countryCode}&country_name=${encodeURIComponent(countryName)}`;
        window.location.href = detailUrl;
    }
    
    // ============ Country Detail Feature Functions ============
    
    function initCountryDetailFeature() {
        const countryData = athanCountryDetail;
        const countryFlags = {
            'AF': '🇦🇫', 'AL': '🇦🇱', 'DZ': '🇩🇿', 'AS': '🇦🇸', 'AD': '🇦🇩', 'AO': '🇦🇴', 'AG': '🇦🇬', 'AR': '🇦🇷', 'AM': '🇦🇲', 'AW': '🇦🇼', 'AU': '🇦🇺', 'AT': '🇦🇹', 'AZ': '🇦🇿', 'BS': '🇧🇸', 'BH': '🇧🇭', 'BD': '🇧🇩', 'BB': '🇧🇧', 'BY': '🇧🇾', 'BE': '🇧🇪', 'BZ': '🇧🇿', 'BJ': '🇧🇯', 'BM': '🇧🇲', 'BT': '🇧🇹', 'BO': '🇧🇴', 'BA': '🇧🇦', 'BW': '🇧🇼', 'BR': '🇧🇷', 'IO': '🇮🇴', 'BN': '🇧🇳', 'BG': '🇧🇬', 'BF': '🇧🇫', 'BI': '🇧🇮', 'KH': '🇰🇭', 'CM': '🇨🇲', 'CA': '🇨🇦', 'CV': '🇨🇻', 'BQ': '🇧🇶', 'KY': '🇰🇾', 'CF': '🇨🇫', 'TD': '🇹🇩', 'CL': '🇨🇱', 'CN': '🇨🇳', 'CX': '🇨🇽', 'CO': '🇨🇴', 'KM': '🇰🇲', 'CK': '🇨🇰', 'CR': '🇨🇷', 'HR': '🇭🇷', 'CU': '🇨🇺', 'CW': '🇨🇼', 'CY': '🇨🇾', 'CZ': '🇨🇿', 'DK': '🇩🇰', 'DJ': '🇩🇯', 'DM': '🇩🇲', 'DO': '🇩🇴', 'CD': '🇨🇩', 'EC': '🇪🇨', 'EG': '🇪🇬', 'SV': '🇸🇻', 'GQ': '🇬🇶', 'ER': '🇪🇷', 'EE': '🇪🇪', 'SZ': '🇸🇿', 'ET': '🇪🇹', 'FK': '🇫🇰', 'FO': '🇫🇴', 'FJ': '🇫🇯', 'FI': '🇫🇮', 'FR': '🇫🇷', 'GF': '🇬🇫', 'PF': '🇵🇫', 'GA': '🇬🇦', 'GM': '🇬🇲', 'GE': '🇬🇪', 'DE': '🇩🇪', 'GH': '🇬🇭', 'GI': '🇬🇮', 'GR': '🇬🇷', 'GL': '🇬🇱', 'GD': '🇬🇩', 'GP': '🇬🇵', 'GU': '🇬🇺', 'GT': '🇬🇹', 'GN': '🇬🇳', 'GW': '🇬🇼', 'GY': '🇬🇾', 'HT': '🇭🇹', 'HN': '🇭🇳', 'HU': '🇭🇺', 'IS': '🇮🇸', 'IN': '🇮🇳', 'ID': '🇮🇩', 'IR': '🇮🇷', 'IQ': '🇮🇶', 'IE': '🇮🇪', 'IL': '🇮🇱', 'IT': '🇮🇹', 'JM': '🇯🇲', 'JP': '🇯🇵', 'JO': '🇯🇴', 'KZ': '🇰🇿', 'KE': '🇰🇪', 'KI': '🇰🇮', 'KP': '🇰🇵', 'KR': '🇰🇷', 'XK': '🇽🇰', 'KW': '🇰🇼', 'KG': '🇰🇬', 'LA': '🇱🇦', 'LV': '🇱🇻', 'LB': '🇱🇧', 'LS': '🇱🇸', 'LR': '🇱🇷', 'LY': '🇱🇾', 'LI': '🇱🇮', 'LT': '🇱🇹', 'LU': '🇱🇺', 'MO': '🇲🇴', 'MG': '🇲🇬', 'MW': '🇲🇼', 'MY': '🇲🇾', 'MV': '🇲🇻', 'ML': '🇲🇱', 'MT': '🇲🇹', 'MH': '🇲🇭', 'MQ': '🇲🇶', 'MR': '🇲🇷', 'MU': '🇲🇺', 'YT': '🇾🇹', 'MX': '🇲🇽', 'FM': '🇫🇲', 'MD': '🇲🇩', 'MC': '🇲🇨', 'MN': '🇲🇳', 'ME': '🇲🇪', 'MS': '🇲🇸', 'MA': '🇲🇦', 'MZ': '🇲🇿', 'MM': '🇲🇲', 'NA': '🇳🇦', 'NR': '🇳🇷', 'NP': '🇳🇵', 'NL': '🇳🇱', 'NC': '🇳🇨', 'NZ': '🇳🇿', 'NI': '🇳🇮', 'NE': '🇳🇪', 'NG': '🇳🇬', 'NU': '🇳🇺', 'NF': '🇳🇫', 'MK': '🇲🇰', 'MP': '🇲🇵', 'NO': '🇳🇴', 'OM': '🇴🇲', 'PK': '🇵🇰', 'PW': '🇵🇼', 'PS': '🇵🇸', 'PA': '🇵🇦', 'PG': '🇵🇬', 'PY': '🇵🇾', 'PE': '🇵🇪', 'PH': '🇵🇭', 'PN': '🇵🇳', 'PL': '🇵🇱', 'PT': '🇵🇹', 'PR': '🇵🇷', 'QA': '🇶🇦', 'RE': '🇷🇪', 'RO': '🇷🇴', 'RU': '🇷🇺', 'RW': '🇷🇼', 'SH': '🇸🇭', 'KN': '🇰🇳', 'LC': '🇱🇨', 'PM': '🇵🇲', 'VC': '🇻🇨', 'WS': '🇼🇸', 'SM': '🇸🇲', 'ST': '🇸🇹', 'SA': '🇸🇦', 'SN': '🇸🇳', 'RS': '🇷🇸', 'SC': '🇸🇨', 'SL': '🇸🇱', 'SG': '🇸🇬', 'SX': '🇸🇽', 'SK': '🇸🇰', 'SI': '🇸🇮', 'SB': '🇸🇧', 'SO': '🇸🇴', 'ZA': '🇿🇦', 'GS': '🇬🇸', 'SS': '🇸🇸', 'ES': '🇪🇸', 'LK': '🇱🇰', 'SD': '🇸🇩', 'SR': '🇸🇷', 'SJ': '🇸🇯', 'SE': '🇸🇪', 'CH': '🇨🇭', 'SY': '🇸🇾', 'TW': '🇹🇼', 'TJ': '🇹🇯', 'TZ': '🇹🇿', 'TH': '🇹🇭', 'TL': '🇹🇱', 'TG': '🇹🇬', 'TK': '🇹🇰', 'TO': '🇹🇴', 'TT': '🇹🇹', 'TN': '🇹🇳', 'TR': '🇹🇷', 'TM': '🇹🇲', 'TC': '🇹🇨', 'TV': '🇹🇻', 'UG': '🇺🇬', 'UA': '🇺🇦', 'AE': '🇦🇪', 'GB': '🇬🇧', 'US': '🇺🇸', 'UY': '🇺🇾', 'UZ': '🇺🇿', 'VU': '🇻🇺', 'VA': '🇻🇦', 'VE': '🇻🇪', 'VN': '🇻🇳', 'WF': '🇼🇫', 'EH': '🇪🇭', 'YE': '🇾🇪', 'ZM': '🇿🇲', 'ZW': '🇿🇼', 'AX': '🇦🇽'
        };
        
        // Set country flag and emblem
        const flag = countryFlags[countryData.code] || '🌍';
        $('#country-flag').text(flag);
        $('#country-emblem').text('🛡️');
        
        // Display capital city current time
        if (countryData.offset) {
            displayCapitalCityTime(countryData.offset);
        }
        
        // Fetch prayer times for all cities
        countryData.cities.forEach(city => {
            fetchCityPrayerTimes(city, countryData.name);
        });
        
        // Back to countries
        $(document).on('click', '#athan-back-to-countries', function(e) {
            e.preventDefault();
            const countriesUrl = (typeof athanData !== 'undefined' && athanData.countriesUrl) ? athanData.countriesUrl : '/countries/';
            window.location.href = countriesUrl;
        });
    }
    
    function displayCapitalCityTime(offset) {
        // Calculate current time in the timezone offset
        const now = new Date();
        
        // Parse offset string like "+04:30" or "-05:00"
        const sign = offset[0] === '+' ? 1 : -1;
        const [offsetHours, offsetMinutes] = offset.slice(1).split(':').map(Number);
        const offsetMs = sign * (offsetHours * 3600 + offsetMinutes * 60) * 1000;
        
        function updateCapitalTime() {
            const now = new Date();
            const utcTime = now.getTime() + now.getTimezoneOffset() * 60 * 1000;
            const localTime = new Date(utcTime + offsetMs);
            
            const timeString = localTime.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            
            $('#capital-current-time').text(timeString);
        }
        
        // Initial update
        updateCapitalTime();
        
        // Update every second
        setInterval(updateCapitalTime, 1000);
    }
    
    function fetchCityPrayerTimes(city, country) {
        $.ajax({
            type: 'POST',
            url: athanData.ajaxUrl,
            data: {
                action: 'get_prayer_times',
                nonce: athanData.nonce,
                city: city,
                country: country
            },
            success: function(response) {
                if (response.success) {
                    const times = response.data;
                    $(`.athan-city-row[data-city="${city}"]`).find('td').each(function(index) {
                        const prayers = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
                        
                        if (index > 0 && index <= prayers.length) {
                            $(this).text(times[prayers[index - 1]]);
                        }
                    });
                }
            }
        });
    }
    
    function initCityDetailFeature() {
        const city = athanCityDetail.city;
        const country = athanCityDetail.country;
        const countryCode = athanCityDetail.country_code;
        const offset = athanCityDetail.offset || '+00:00';
        const capital = athanCityDetail.capital || '';
        
        // Get countries data to find flag and emblem
        const countriesData = athanCountriesData || [];
        let countryInfo = null;
        
        for (let c of countriesData) {
            if (c.code === countryCode) {
                countryInfo = c;
                break;
            }
        }
        
        // Update city heading
        $('#athan-city-name').text(`Prayer times in ${city}, ${country}`);
        
        // Update timezone offset
        $('#athan-city-offset').text(offset);
        
        // Set flag and emblem images from countries.json
        if (countryInfo) {
            if (countryInfo.flag) {
                $('#athan-city-flag-img').attr('src', countryInfo.flag);
            }
            if (countryInfo.emblem) {
                $('#athan-city-emblem-img').attr('src', countryInfo.emblem);
            }
        }
        
        // Get current date
        const today = new Date();
        const day = today.getDate();
        const month = today.getMonth() + 1;
        const year = today.getFullYear();
        
        // Update date display
        const dateString = today.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        $('#athan-city-date').text(dateString);
        
        // Fetch prayer times for the city
        $.ajax({
            type: 'POST',
            url: athanData.ajaxUrl,
            data: {
                action: 'get_prayer_times',
                nonce: athanData.nonce,
                city: city,
                country: country
            },
            success: function(response) {
                if (response.success) {
                    const times = response.data;
                    const prayers = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
                    
                    prayers.forEach(prayer => {
                        const selector = '#athan-' + prayer.toLowerCase();
                        if ($(selector).length) {
                            $(selector).text(times[prayer] || '--:--');
                        }
                    });
                    
                    // Update Hijri date if available
                    if (times.hijri_date) {
                        const hijriDateStr = times.hijri_date.day + ' ' + times.hijri_date.month.en + ' ' + times.hijri_date.year;
                        $('#athan-hijri-date').text('Hijri: ' + hijriDateStr);
                    }
                    
                    // Update Gregorian date if available
                    if (times.gregorian_date) {
                        const gregDateStr = times.gregorian_date.day + ' ' + times.gregorian_date.month.en + ' ' + times.gregorian_date.year;
                        $('#athan-gregorian-date').text('Gregorian: ' + gregDateStr);
                    }
                }
            },
            error: function() {
                console.error('Failed to fetch prayer times for', city);
            }
        });
        
        // Fetch monthly calendar data
        $.ajax({
            type: 'POST',
            url: athanData.ajaxUrl,
            data: {
                action: 'get_monthly_calendar',
                nonce: athanData.nonce,
                city: city,
                country: country,
                month: month,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    const monthlyData = response.data;
                    const todayDate = today.getDate();
                    let tableHTML = '';
                    let hijriMonthName = '';
                    
                    monthlyData.forEach((day, index) => {
                        const prayers = day.timings;
                        const hijriDay = day.date.hijri.day;
                        const gregDay = parseInt(day.date.gregorian.day);
                        const weekday = day.date.gregorian.weekday.en.substring(0, 3);
                        const isToday = gregDay === todayDate;
                        const rowClass = isToday ? 'athan-today-row' : '';
                        
                        // Get Hijri month name from first day
                        if (index === 0) {
                            hijriMonthName = day.date.hijri.month.en;
                            $('#athan-hijri-month-name').text(hijriMonthName);
                            // Update table header with Hijri month name
                            $('.athan-city-monthly-table thead th:eq(1)').text(hijriMonthName);
                        }
                        
                        // Remove timezone offset from prayer times (e.g., "05:32 +07" -> "05:32")
                        const fajr = (prayers.Fajr || '--:--').split(' ')[0];
                        const sunrise = (prayers.Sunrise || '--:--').split(' ')[0];
                        const dhuhr = (prayers.Dhuhr || '--:--').split(' ')[0];
                        const asr = (prayers.Asr || '--:--').split(' ')[0];
                        const maghrib = (prayers.Maghrib || '--:--').split(' ')[0];
                        const isha = (prayers.Isha || '--:--').split(' ')[0];
                        
                        tableHTML += `<tr class="${rowClass}">
                            <td>${gregDay.toString().padStart(2, '0')} ${weekday}</td>
                            <td>${hijriDay}</td>
                            <td>${fajr}</td>
                            <td>${sunrise}</td>
                            <td>${dhuhr}</td>
                            <td>${asr}</td>
                            <td>${maghrib}</td>
                            <td>${isha}</td>
                        </tr>`;
                    });
                    
                    $('#athan-monthly-tbody').html(tableHTML);
                }
            },
            error: function() {
                $('#athan-monthly-tbody').html('<tr><td colspan="8" class="error">Failed to load monthly data</td></tr>');
                console.error('Failed to fetch monthly calendar for', city);
            }
        });
        
        // Display current time
        function updateCityTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            $('#athan-city-time').text(timeString);
            
            // Highlight next prayer
            highlightNextPrayer();
        }
        
        function highlightNextPrayer() {
            const now = new Date();
            const currentTime = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const prayers = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
            let nextPrayer = null;
            
            // Get all prayer time elements
            const prayerTimes = {};
            prayers.forEach(prayer => {
                const timeText = $('#athan-' + prayer.toLowerCase()).text();
                prayerTimes[prayer] = timeText;
            });
            
            // Find next prayer
            for (const prayer of prayers) {
                const prayerTime = prayerTimes[prayer];
                if (compareTimes(currentTime, prayerTime) < 0) {
                    nextPrayer = prayer;
                    break;
                }
            }
            
            // If no prayer found, next is Fajr
            if (!nextPrayer) {
                nextPrayer = 'Fajr';
            }
            
            // Remove highlight from all prayer items
            $('.prayer-item').removeClass('next-prayer-highlight');
            
            // Add highlight to next prayer
            $('#athan-' + nextPrayer.toLowerCase()).closest('.prayer-item').addClass('next-prayer-highlight');
        }
        
        function compareTimes(time1, time2) {
            const [h1, m1] = time1.split(':').map(Number);
            const [h2, m2] = time2.split(':').map(Number);
            
            if (h1 !== h2) return h1 - h2;
            return m1 - m2;
        }
        
        updateCityTime();
        setInterval(updateCityTime, 1000);
        
        // Update next prayer countdown
        function updateNextPrayerCountdown() {
            const now = new Date();
            const currentTime = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            const prayers = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
            let nextPrayer = null;
            let nextPrayerTime = null;
            
            // Get all prayer times
            const prayerTimes = {};
            prayers.forEach(prayer => {
                const timeText = $('#athan-' + prayer.toLowerCase()).text();
                prayerTimes[prayer] = timeText;
            });
            
            // Find next prayer
            for (const prayer of prayers) {
                const prayerTime = prayerTimes[prayer];
                if (compareTimes(currentTime, prayerTime) < 0) {
                    nextPrayer = prayer;
                    nextPrayerTime = prayerTime;
                    break;
                }
            }
            
            // If no more prayers today, show first prayer of next day
            if (!nextPrayer) {
                nextPrayer = 'Fajr';
                nextPrayerTime = prayerTimes.Fajr;
            }
            
            // Calculate time difference
            const [nextHour, nextMinute, nextSecond] = nextPrayerTime.split(':');
            const nextDate = new Date(now);
            nextDate.setHours(parseInt(nextHour), parseInt(nextMinute), 0, 0);
            
            // If next prayer time has passed, add 24 hours
            if (nextDate <= now) {
                nextDate.setDate(nextDate.getDate() + 1);
            }
            
            const diffMs = nextDate - now;
            const diffSec = Math.floor(diffMs / 1000);
            
            const hours = Math.floor(diffSec / 3600);
            const minutes = Math.floor((diffSec % 3600) / 60);
            const seconds = diffSec % 60;
            
            const countdownText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            $('#athan-next-prayer-countdown').text(countdownText);
        }
        
        updateNextPrayerCountdown();
        setInterval(updateNextPrayerCountdown, 1000);
        
        // Set city image (using city name in placeholder)
        const imageUrl = `https://via.placeholder.com/800x400?text=${encodeURIComponent(city + ', ' + country)}`;
        $('#athan-city-image').attr('src', imageUrl);
        
        // Handle organized cities search
        $('#athan-cities-search-organized-detail').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if (!searchTerm) {
                // Show all groups
                $('.athan-letter-group').show();
                return;
            }
            
            // Hide all groups initially
            $('.athan-letter-group').hide();
            
            // Show groups that have matching cities
            $('.athan-letter-group').each(function() {
                let hasMatch = false;
                $(this).find('.athan-city-link-organized').each(function() {
                    const cityName = $(this).text().toLowerCase();
                    if (cityName.includes(searchTerm)) {
                        $(this).show();
                        hasMatch = true;
                    } else {
                        $(this).hide();
                    }
                });
                
                // Show group if it has matching cities
                if (hasMatch) {
                    $(this).show();
                }
            });
        });
    }
    
    // Handle search in country detail page for organized cities
    $(document).on('keyup', '#athan-cities-search-organized-detail', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        if (!searchTerm) {
            // Show all groups and all city links
            $('.athan-cities-organized .athan-letter-group').show();
            $('.athan-cities-organized .athan-city-link-organized').show();
            return;
        }
        
        // Hide all groups initially
        $('.athan-cities-organized .athan-letter-group').hide();
        
        // Show groups that have matching cities
        $('.athan-cities-organized .athan-letter-group').each(function() {
            let hasMatch = false;
            $(this).find('.athan-city-link-organized').each(function() {
                const cityName = $(this).text().toLowerCase();
                if (cityName.includes(searchTerm)) {
                    $(this).show();
                    hasMatch = true;
                } else {
                    $(this).hide();
                }
            });
            
            // Show group if it has matching cities
            if (hasMatch) {
                $(this).show();
            }
        });
    });
});
