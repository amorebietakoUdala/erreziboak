import {
   Controller
} from '@hotwired/stimulus';

import { Modal } from 'bootstrap';

export default class extends Controller {
   static targets = ['modal', 'modalTitle', "modalBody"];
   static values = {
      title: String,
   };


   async openModal(e) { 
      event.preventDefault();
      this.modalBodyTarget.innerHTML = 'Loading...';
      this.modal = new Modal(this.modalTarget);
      this.modal.show();
      const response = await $.ajax(e.currentTarget.href);
      this.modalTitleTarget.innerHTML = this.titleValue;
      this.modalBodyTarget.innerHTML = response;
   }
}
