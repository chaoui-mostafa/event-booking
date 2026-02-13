import axios from 'axios';

class AuthService {
    constructor() {
        this.token = localStorage.getItem('token');
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
        this.setupAxios();
    }

    setupAxios() {
        // Set default axios config
        axios.defaults.baseURL = window.location.origin;
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.headers.common['Content-Type'] = 'application/json';

        // Add token to requests if exists
        if (this.token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        }

        // Add response interceptor to handle 401
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    this.logout();
                    if (!window.location.pathname.includes('/login')) {
                        window.location.href = '/login';
                    }
                }
                return Promise.reject(error);
            }
        );
    }

    async login(credentials) {
        try {
            const response = await axios.post('/api/v1/login', credentials);

            if (response.data.success) {
                this.token = response.data.data.token;
                this.user = response.data.data.user;

                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                // Update axios headers
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                return { success: true, data: response.data };
            }
        } catch (error) {
            return {
                success: false,
                error: error.response?.data || { message: 'Login failed' }
            };
        }
    }

    async register(userData) {
        try {
            const response = await axios.post('/api/v1/register', userData);

            if (response.data.success) {
                this.token = response.data.data.token;
                this.user = response.data.data.user;

                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                return { success: true, data: response.data };
            }
        } catch (error) {
            return {
                success: false,
                error: error.response?.data || { message: 'Registration failed' }
            };
        }
    }

    async logout() {
        try {
            if (this.token) {
                await axios.post('/api/v1/logout');
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        }
    }

    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    getUser() {
        return this.user;
    }

    getToken() {
        return this.token;
    }
}

export default new AuthService();
