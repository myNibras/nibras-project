import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SharedPopupComponent } from './shared-popup.component';

describe('SharedPopupComponent', () => {
  let component: SharedPopupComponent;
  let fixture: ComponentFixture<SharedPopupComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SharedPopupComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SharedPopupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
