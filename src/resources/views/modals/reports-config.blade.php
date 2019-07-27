<div class="modal fade" id="reports-config-modal" tabindex="-1" role="dialog" aria-labelledby="reports-configLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="reports-configLabel">Report Configuration (@{{ config.display_name }})</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<h4>The following account(s) will be included in the @{{ config.display_name }} report:</h4>
                <ul>
                    <li v-for="account in config.accounts.data" :key="'report-' + config.id + '-account-' + account.id">
                        @{{ account.display_name }} (@{{ account.entry_type }})
                        <span v-if="typeof account.parent_account.data !== 'undefined'">- @{{ account.parent_account.data.display_name }}</span>
                    </li>
                </ul>
                <p>If you need to add/remove accounts, close this window and use the <strong>Edit Configuration</strong> button</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>