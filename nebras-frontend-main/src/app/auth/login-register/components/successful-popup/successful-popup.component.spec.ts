import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuccessfulPopupComponent } from './successful-popup.component';

describe('SuccessfulPopupComponent', () => {
  let component: SuccessfulPopupComponent;
  let fixture: ComponentFixture<SuccessfulPopupComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SuccessfulPopupComponent]
    })
      .compileComponents();

    fixture = TestBed.createComponent(SuccessfulPopupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
