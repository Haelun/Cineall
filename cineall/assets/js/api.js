const API = {
    baseURL: 'api',

    async request(endpoint, params = {}, method = 'GET') {
        try {
            let url = `${this.baseURL}/${endpoint}`;

            if (method === 'GET' && Object.keys(params).length > 0) {
                const queryString = new URLSearchParams(params).toString();
                url += `?${queryString}`;
            }

            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (method === 'POST' && Object.keys(params).length > 0) {
                options.body = JSON.stringify(params);
            }

            const response = await fetch(url, options);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'API request failed');
            }

            return data.data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    movies: {
        list(filters = {}) {
            return API.request('movies.php', { action: 'list', ...filters });
        },

        detail(id) {
            return API.request('movies.php', { action: 'detail', id });
        },

        detailByKey(key) {
            return API.request('movies.php', { action: 'detail', key });
        },

        search(query, limit = 10) {
            return API.request('movies.php', { action: 'search', q: query, limit });
        },

        byGenre(genre) {
            return API.request('movies.php', { action: 'by_genre', genre });
        },

        homeRows() {
            return API.request('movies.php', { action: 'home_rows' });
        }
    },

    platforms: {
        list() {
            return API.request('platforms.php', { action: 'list' });
        },

        stats() {
            return API.request('platforms.php', { action: 'stats' });
        }
    },

    genres: {
        list() {
            return API.request('genres.php', { action: 'list' });
        }
    },

    user: {
        getWatchlist() {
            return API.request('user.php', { action: 'get_watchlist' });
        },

        addToWatchlist(movieId) {
            const formData = new FormData();
            formData.append('action', 'add_to_watchlist');
            formData.append('movie_id', movieId);

            return fetch(`${API.baseURL}/user.php`, {
                method: 'POST',
                body: formData
            }).then(res => res.json());
        },

        removeFromWatchlist(movieId) {
            const formData = new FormData();
            formData.append('action', 'remove_from_watchlist');
            formData.append('movie_id', movieId);

            return fetch(`${API.baseURL}/user.php`, {
                method: 'POST',
                body: formData
            }).then(res => res.json());
        },

        getSubscriptions() {
            return API.request('user.php', { action: 'get_subscriptions' });
        },

        toggleSubscription(platformId) {
            const formData = new FormData();
            formData.append('action', 'toggle_subscription');
            formData.append('platform_id', platformId);

            return fetch(`${API.baseURL}/user.php`, {
                method: 'POST',
                body: formData
            }).then(res => res.json());
        },

        getPreferences() {
            return API.request('user.php', { action: 'get_preferences' });
        },

        updatePreferences(prefs) {
            const formData = new FormData();
            formData.append('action', 'update_preferences');
            Object.keys(prefs).forEach(key => {
                formData.append(key, prefs[key]);
            });

            return fetch(`${API.baseURL}/user.php`, {
                method: 'POST',
                body: formData
            }).then(res => res.json());
        }
    }
};
