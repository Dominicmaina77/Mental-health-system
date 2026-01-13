// API Service for SootheSpace
class ApiService {
    constructor() {
        this.baseUrl = 'http://localhost/Mental-health-system/backend';
        this.token = localStorage.getItem('token') || sessionStorage.getItem('token');
    }

    // Set token in storage
    setToken(token, rememberMe = false) {
        if (rememberMe) {
            localStorage.setItem('token', token);
        } else {
            sessionStorage.setItem('token', token);
        }
        this.token = token;
    }

    // Remove token from storage
    removeToken() {
        localStorage.removeItem('token');
        sessionStorage.removeItem('token');
        this.token = null;
    }

    // Generic API request method
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        // Add authorization header if token exists
        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, config);

            // Handle different response statuses
            if (response.status === 401) {
                // Unauthorized - clear token and redirect to login
                this.removeToken();
                window.location.href = 'login.html';
                return null;
            }

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Test API connection
    async testConnection() {
        try {
            const response = await fetch(`${this.baseUrl}/`);
            if (response.ok) {
                return await response.json();
            } else {
                throw new Error(`API responded with status: ${response.status}`);
            }
        } catch (error) {
            console.error('API Connection test failed:', error);
            throw error;
        }
    }

    // AUTHENTICATION METHODS
    async register(userData) {
        return this.request('/api/auth', {
            method: 'POST',
            body: JSON.stringify({
                action: 'register',
                ...userData
            })
        });
    }

    async login(credentials) {
        return this.request('/api/auth', {
            method: 'POST',
            body: JSON.stringify({
                action: 'login',
                ...credentials
            })
        });
    }

    async logout() {
        try {
            await this.request('/api/auth', {
                method: 'POST',
                body: JSON.stringify({ action: 'logout' })
            });
        } finally {
            this.removeToken();
        }
    }

    async getAuthStatus() {
        return this.request('/api/auth', {
            method: 'GET'
        });
    }

    async updateProfile(profileData) {
        return this.request('/api/auth', {
            method: 'POST',
            body: JSON.stringify({
                action: 'update_profile',
                ...profileData
            })
        });
    }

    async updatePassword(passwordData) {
        return this.request('/api/auth', {
            method: 'POST',
            body: JSON.stringify({
                action: 'update_password',
                ...passwordData
            })
        });
    }

    // MOOD TRACKING METHODS
    async createMoodEntry(moodData) {
        return this.request('/api/mood', {
            method: 'POST',
            body: JSON.stringify(moodData)
        });
    }

    async getMoodEntries(limit = 30) {
        const params = new URLSearchParams({ limit });
        return this.request(`/api/mood?${params}`);
    }

    async getMoodEntryByDate(date) {
        const params = new URLSearchParams({ date });
        return this.request(`/api/mood?${params}`);
    }

    async getMoodEntriesByDateRange(startDate, endDate) {
        const params = new URLSearchParams({ start_date: startDate, end_date: endDate });
        return this.request(`/api/mood?${params}`);
    }

    async updateMoodEntry(moodData) {
        return this.request('/api/mood', {
            method: 'PUT',
            body: JSON.stringify(moodData)
        });
    }

    async deleteMoodEntry(entryId) {
        return this.request('/api/mood', {
            method: 'DELETE',
            body: JSON.stringify({ id: entryId })
        });
    }

    async getMoodStreak() {
        const params = new URLSearchParams({ streak: 'true' });
        return this.request(`/api/mood?${params}`);
    }

    // JOURNAL METHODS
    async createJournalEntry(journalData) {
        return this.request('/api/journal', {
            method: 'POST',
            body: JSON.stringify(journalData)
        });
    }

    async getJournalEntries(page = 1, limit = 20) {
        const params = new URLSearchParams({ page, limit });
        return this.request(`/api/journal?${params}`);
    }

    async getJournalEntryById(id) {
        const params = new URLSearchParams({ id });
        return this.request(`/api/journal?${params}`);
    }

    async updateJournalEntry(journalData) {
        return this.request('/api/journal', {
            method: 'PUT',
            body: JSON.stringify(journalData)
        });
    }

    async deleteJournalEntry(entryId) {
        return this.request('/api/journal', {
            method: 'DELETE',
            body: JSON.stringify({ id: entryId })
        });
    }

    async searchJournalEntries(searchTerm, page = 1, limit = 20) {
        const params = new URLSearchParams({ search: searchTerm, page, limit });
        return this.request(`/api/journal?${params}`);
    }

    // REMINDERS METHODS
    async createReminder(reminderData) {
        return this.request('/api/reminders', {
            method: 'POST',
            body: JSON.stringify(reminderData)
        });
    }

    async getReminders() {
        return this.request('/api/reminders');
    }

    async getReminderById(id) {
        const params = new URLSearchParams({ id });
        return this.request(`/api/reminders?${params}`);
    }

    async getTodaysReminders() {
        const params = new URLSearchParams({ today: 'true' });
        return this.request(`/api/reminders?${params}`);
    }

    async getUpcomingReminders() {
        const params = new URLSearchParams({ upcoming: 'true' });
        return this.request(`/api/reminders?${params}`);
    }

    async updateReminder(reminderData) {
        return this.request('/api/reminders', {
            method: 'PUT',
            body: JSON.stringify(reminderData)
        });
    }

    async deleteReminder(reminderId) {
        return this.request('/api/reminders', {
            method: 'DELETE',
            body: JSON.stringify({ id: reminderId })
        });
    }

    // INSIGHTS METHODS
    async getInsights() {
        return this.request('/api/insights');
    }
}

// Create global API instance
const apiService = new ApiService();

// Export for module systems (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ApiService;
}