import { Component, Input } from '@angular/core';

import { Breadcrumb } from './types/breadcrumb.types';
import {TranslatePipe} from "@ngx-translate/core";
import {RouterLink} from "@angular/router";

@Component({
    selector: 'app-breadcrumb',
    imports: [
        TranslatePipe,
        RouterLink
    ],
    templateUrl: './breadcrumb.component.html',
    styleUrl: './breadcrumb.component.scss'
})
export class BreadcrumbComponent {
  @Input({ required: true }) data: Breadcrumb[] = []; 
}
