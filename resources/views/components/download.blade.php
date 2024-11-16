<div class="download modal">
    <div class="modal-inner">
        <div class="modal-contents">
            <div class="download-header">
                <p class="modal-title"><span class="material-symbols-outlined icon">arrow_circle_right</span><span>期間を選択してください</span></p>
                <p class="download-close"><span class="material-symbols-outlined icon">close</span></p>
            </div>
            <form action="export" method="POST">
                @csrf
                <div class="download-selections">
                    <div class="datepickers">
                        <div class="datepicker">
                            <input type="text" id="from" class="input-date" name="from" data-input="0" placeholder="開始日" autocomplete="off">
                        </div>
                        <div>
                            <p><span class="material-symbols-outlined icon">arrow_right</span></p>
                        </div>
                        <div class="datepicker">
                            <input type="text" id="to" class="input-date" name="to" data-input="0" placeholder="終了日" autocomplete="off">
                        </div>
                    </div>
                    <label for="submit-btn" class="submit-label">
                        <span class="material-symbols-outlined thin">download</span>
                    </label>
                    <input type="submit" id="submit-btn" value="" />
                </div>
            </form>
        </div>
    </div>
</div>