// const PlannerPage = () => import(
//     '@/components/planner/PlannerPage.vue'
// );

// const PlannerCalendar = () => import(
//     '@/components/planner/PlannerCalendar.vue'
// );

// const PlannerTasks = () => import(
//      webpackChunkName: "planner"  '@/components/planner/PlannerTasks.vue'
// );

// const PlannerEvents = () => import(
//     '@/components/planner/PlannerEvents.vue'
// );

// const TemporaryPage = () => import(
//     '@/components/planner/TemporaryPage.vue'
// );

const TodosPage = () => import(
    '@/components/todos/TodosPage.vue'
);

const CalendarPage = () => import(
    '@/components/events/CalendarPage.vue'
);

const DocumentsPage = () => import(
    '@/components/documents/DocumentsPage.vue'
);

const NotesPage = () => import(
    '@/components/notes/NotesPage.vue'
);

const PinboardPage = () => import(
    '@/components/pinboard/PinboardPage.vue'
);

const LinksPage = () => import(
    '@/components/links/LinksPage.vue'
);

const EmailsPage = () => import(
    '@/components/emails/EmailsPage.vue'
);

const TimekeeperPage = () => import(
    '@/components/timekeeper/TimekeeperPage.vue'
);

const featuresRoutes = [
    {
        path: '/todos/:providerId?/:listId?',
        name: 'todos',
        component: TodosPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/calendar/:providerId?/:listId?',
        name: 'calendar',
        component: CalendarPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/documents/:listId?',
        name: 'documents',
        component: DocumentsPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/notes/:listId?',
        name: 'notes',
        component: NotesPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/pinboard/:listId?',
        name: 'pinboard',
        component: PinboardPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/links/:listId?',
        name: 'links',
        component: LinksPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/emails/:providerId?/:listId?',
        name: 'emails',
        component: EmailsPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/timekeeper',
        name: 'timekeeper',
        component: TimekeeperPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
];

export default featuresRoutes;
