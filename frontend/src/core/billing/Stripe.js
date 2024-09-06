const stripe = window.Stripe(import.meta.env.VITE_STRIPE_KEY);

export default {
    stripe,
};
