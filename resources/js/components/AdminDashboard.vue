<template>
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Employee Management Dashboard</h1>
        <div
            v-if="globalError"
            class="mb-4 text-red-600 bg-red-100 border border-red-300 rounded p-2"
        >
            {{ globalError }}
        </div>
        <!-- Controls: Search, Filter, Sort -->
        <div class="flex flex-wrap gap-4 mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Search by name or email"
                class="border rounded px-3 py-2"
            />
            <select v-model="department" class="border rounded px-3 py-2">
                <option value="">All Departments</option>
                <option
                    v-for="dept in departments"
                    :key="dept.id"
                    :value="dept.id"
                >
                    {{ dept.name }}
                </option>
            </select>
            <input
                v-model.number="salaryMin"
                type="number"
                placeholder="Min Salary"
                class="border rounded px-3 py-2 w-32"
            />
            <input
                v-model.number="salaryMax"
                type="number"
                placeholder="Max Salary"
                class="border rounded px-3 py-2 w-32"
            />
            <select v-model="sort" class="border rounded px-3 py-2">
                <option value="">Sort by Joining Date</option>
                <option value="asc">Oldest First</option>
                <option value="desc">Newest First</option>
            </select>
            <button
                @click="openForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Add Employee
            </button>
        </div>
        <!-- Loading Indicator -->
        <div v-if="loading" class="text-center py-4">Loading...</div>
        <!-- Employee Table -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full bg-white rounded shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Department</th>
                        <th class="px-4 py-2">Designation</th>
                        <th class="px-4 py-2">Joining Date</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="employee in employees" :key="employee.id">
                        <td class="border px-4 py-2">{{ employee.name }}</td>
                        <td class="border px-4 py-2">{{ employee.email }}</td>
                        <td class="border px-4 py-2">
                            {{ employee.department?.name }}
                        </td>
                        <td class="border px-4 py-2">
                            {{ employee.detail?.designation }}
                        </td>
                        <td class="border px-4 py-2">
                            {{ employee.detail?.joined_date }}
                        </td>
                        <td class="border px-4 py-2 flex gap-2">
                            <button
                                @click="openForm(employee)"
                                class="text-blue-600 hover:underline"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteEmployee(employee.id)"
                                class="text-red-600 hover:underline"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center gap-2">
            <button
                :disabled="page === 1 || loading"
                @click="page--"
                class="px-3 py-1 border rounded"
            >
                Prev
            </button>
            <span>Page {{ page }}</span>
            <button
                :disabled="!hasMore || loading"
                @click="page++"
                class="px-3 py-1 border rounded"
            >
                Next
            </button>
        </div>
        <!-- Employee Form Modal (with loading) -->
        <div
            v-if="showForm"
            class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
        >
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
                <h2 class="text-xl font-bold mb-4">
                    {{ formEmployee.id ? "Edit" : "Add" }} Employee
                </h2>
                <form @submit.prevent="submitForm">
                    <div class="mb-2">
                        <label class="block mb-1">Name</label>
                        <input
                            v-model="formEmployee.name"
                            type="text"
                            class="border rounded px-3 py-2 w-full"
                            required
                        />
                        <div v-if="errors.name" class="text-red-600 text-sm">
                            {{ errors.name[0] }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block mb-1">Email</label>
                        <input
                            v-model="formEmployee.email"
                            type="email"
                            class="border rounded px-3 py-2 w-full"
                            required
                        />
                        <div v-if="errors.email" class="text-red-600 text-sm">
                            {{ errors.email[0] }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block mb-1">Department</label>
                        <select
                            v-model="formEmployee.department_id"
                            class="border rounded px-3 py-2 w-full"
                            required
                        >
                            <option value="">Select Department</option>
                            <option
                                v-for="dept in departments"
                                :key="dept.id"
                                :value="dept.id"
                            >
                                {{ dept.name }}
                            </option>
                        </select>
                        <div
                            v-if="errors.department_id"
                            class="text-red-600 text-sm"
                        >
                            {{ errors.department_id[0] }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block mb-1">Designation</label>
                        <input
                            v-model="formEmployee.designation"
                            type="text"
                            class="border rounded px-3 py-2 w-full"
                            required
                        />
                        <div
                            v-if="errors.designation"
                            class="text-red-600 text-sm"
                        >
                            {{ errors.designation[0] }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block mb-1">Salary</label>
                        <input
                            v-model="formEmployee.salary"
                            type="number"
                            class="border rounded px-3 py-2 w-full"
                            required
                        />
                        <div v-if="errors.salary" class="text-red-600 text-sm">
                            {{ errors.salary[0] }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block mb-1">Address</label>
                        <input
                            v-model="formEmployee.address"
                            type="text"
                            class="border rounded px-3 py-2 w-full"
                            required
                        />
                        <div v-if="errors.address" class="text-red-600 text-sm">
                            {{ errors.address[0] }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block mb-1">Joining Date</label>
                        <input
                            v-model="formEmployee.joined_date"
                            type="date"
                            class="border rounded px-3 py-2 w-full"
                            required
                        />
                        <div
                            v-if="errors.joined_date"
                            class="text-red-600 text-sm"
                        >
                            {{ errors.joined_date[0] }}
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button
                            type="submit"
                            :disabled="formLoading"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            {{ formEmployee.id ? "Update" : "Create" }}
                        </button>
                        <button
                            type="button"
                            @click="closeForm"
                            :disabled="formLoading"
                            class="bg-gray-300 px-4 py-2 rounded"
                        >
                            Cancel
                        </button>
                    </div>
                    <div v-if="formLoading" class="text-center py-2">
                        Saving...
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
axios.defaults.withCredentials = true;

const employees = ref([]);
const departments = ref([]);
const search = ref("");
const department = ref("");
const salaryMin = ref("");
const salaryMax = ref("");
const sort = ref("");
const page = ref(1);
const hasMore = ref(true);
const showForm = ref(false);
const formEmployee = ref({});
const errors = ref({});
const loading = ref(false);
const formLoading = ref(false);
const globalError = ref("");

const fetchDepartments = async () => {
    try {
        loading.value = true;
        const res = await axios.get("/api/departments");
        departments.value = res.data;
    } catch (e) {
        globalError.value = "Failed to load departments.";
    } finally {
        loading.value = false;
    }
};

const fetchEmployees = async () => {
    try {
        loading.value = true;
        globalError.value = "";
        const params = {
            search: search.value,
            department_id: department.value,
            salary_min: salaryMin.value,
            salary_max: salaryMax.value,
            sort: sort.value,
            page: page.value,
        };
        const res = await axios.get("/api/employees", { params });
        employees.value = res.data.data;
        hasMore.value = !!res.data.next_page_url;
    } catch (e) {
        globalError.value = "Failed to load employees.";
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDepartments();
    fetchEmployees();
});

watch([search, department, salaryMin, salaryMax, sort, page], fetchEmployees);

function openForm(employee = {}) {
    formEmployee.value = { ...employee };
    showForm.value = true;
    errors.value = {};
}
function closeForm() {
    showForm.value = false;
    formEmployee.value = {};
    errors.value = {};
}
function deleteEmployee(id) {
    if (confirm("Are you sure you want to delete this employee?")) {
        loading.value = true;
        axios
            .delete(`/api/employees/${id}`)
            .then(fetchEmployees)
            .catch(() => {
                globalError.value = "Failed to delete employee.";
            })
            .finally(() => {
                loading.value = false;
            });
    }
}

async function submitForm() {
    errors.value = {};
    formLoading.value = true;
    globalError.value = "";
    try {
        if (formEmployee.value.id) {
            // Update
            await axios.put(
                `/api/employees/${formEmployee.value.id}`,
                formEmployee.value
            );
        } else {
            // Create
            await axios.post("/api/employees", formEmployee.value);
        }
        closeForm();
        fetchEmployees();
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            globalError.value = "Failed to save employee.";
        }
    } finally {
        formLoading.value = false;
    }
}
</script>

<style scoped>
/* Add any custom styles here */
</style>
