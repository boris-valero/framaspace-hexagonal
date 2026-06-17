<template>
	<NcSettingsSection
		:name="t('framaspace', 'Navigation visibility')"
		:description="t('framaspace', 'Choose which applications are hidden from the top navigation bar.')">
		<div id="framaspace-admin-settings">
			<table>
				<thead>
					<tr>
						<th>{{ t('framaspace', 'Application') }}</th>
						<th>{{ t('framaspace', 'Hide icon') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="app in apps" :key="app.id">
						<td>{{ app.name }}</td>
						<td>
							<NcCheckboxRadioSwitch
								v-model="app.hidden"
								:disabled="app.protected"
								:title="app.protected ? t('framaspace', 'This application cannot be hidden') : ''" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</NcSettingsSection>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { NcCheckboxRadioSwitch, NcSettingsSection } from '@nextcloud/vue'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import '@nextcloud/dialogs/style.css'

const apps = ref(loadState('framaspace', 'apps', []))
const hiddenUrl = generateUrl('/apps/framaspace/api/v1/admin/hidden')

const save = async () => {
	try {
		const payload = new URLSearchParams()
		apps.value
			.filter(a => a.hidden)
			.forEach((a) => payload.append('hidden[]', a.id))

		await axios.post(hiddenUrl, payload, {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		})
		showSuccess(t('framaspace', 'Saved!'))
	} catch (e) {
		showError(t('framaspace', 'Save error'))
	}
}

// Trigger save on any app hidden state change
watch(
	() => apps.value.map(a => a.hidden),
	() => save(),
	{ deep: true }
)
</script>

<style scoped lang="scss">
#framaspace-admin-settings {
	max-width: 700px;

	table {
		width: 100%;
		border-collapse: collapse;
		margin: calc(var(--default-grid-baseline) * 5) 0;
		table-layout: fixed;
	}

	th,
	td {
		padding: calc(var(--default-grid-baseline) * 4);
		text-align: left;
		border-bottom: var(--border-width-input) solid var(--color-border);
		vertical-align: middle;
	}

	th:nth-child(1),
	td:nth-child(1) {
		width: 60%;
	}

	th:nth-child(2),
	td:nth-child(2) {
		width: 40%;
		text-align: center;
	}

	th {
		background-color: var(--color-background-hover);
		font-weight: bold;
	}
}
</style>
